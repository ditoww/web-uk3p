<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\DevTicket;

class SupportTicket extends Model
{
    use HasFactory;

    // Status constants for consistency across the codebase
    public const STATUS_OPEN = 'Open';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_RESOLVED = 'Resolved';

    protected $table = 'devTickets';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'supportID',
        'devID',
        'roleID',
        'title',
        'status',
        'link',
        'created_at',
        'updated_at',
    ];

    // Ambil semua data devTickets
    public function allData()
    {
        return DB::table($this->table)->where('devTickets.isDeleted', '=', 0)->get();
    }

    // Ambil data dengan join ke user support dan developer
    public function allWithUsers()
    {
        return DB::table($this->table)
            ->leftJoin('users as support', 'devTickets.supportID', '=', 'support.id')
            ->leftJoin('users as dev', 'devTickets.devID', '=', 'dev.id')
            ->leftJoin('devRoles as role', 'devTickets.roleID', '=', 'role.id')
            ->where('devTickets.isDeleted', '=', 0)
            ->select(
                'devTickets.*',
                DB::raw("COALESCE(support.name, support.username) as support"),
                DB::raw("COALESCE(dev.name, dev.username) as dev"),
                'role.roleName as role'
            )
            ->get();
    }

    // Model SupportTicket
    public function allWithDevRole($status = null)
    {
        $query = DB::table($this->table)
            ->leftJoin('devRoles', 'devTickets.roleID', '=', 'devRoles.id')
            ->leftJoin('users as support', 'devTickets.supportID', '=', 'support.id')
            ->where('devTickets.isDeleted', '=', 0);

        if ($status !== null) {
            $query->where('devTickets.status', $status);
        }

        return $query->select(
            'devTickets.id',
            'devTickets.title',
            'devTickets.status',
            'devTickets.created_at',
            'devTickets.updated_at',
            'support.profile_picture AS user_avatar',
            'devRoles.roleName',
            'support.name as support_name',
            DB::raw('(SELECT response FROM devChats WHERE devChats.ticket_id = devTickets.id ORDER BY devChats.created_at ASC LIMIT 1) as first_response')
        )->get();
    }



    // Tambah data
    public function addData($data)
    {
        return DB::table($this->table)->insert($data);
    }

    public function deleteData($id)
    {
        $ticket = DevTicket::findOrFail($id);
        $ticket->isDeleted = 1;
        $ticket->status = "Deleted";
        $ticket->save();
        return back();
    }

    /**
     * Klaim tiket oleh developer: set devID dan ubah status dari 'open' ke 'in progress'.
     * Menggunakan transaksi + lock agar aman terhadap race condition.
     * @param int $ticketId
     * @param int $devId
     * @return object|null  Ticket terbaru jika sukses, null jika gagal (sudah diklaim / tidak ditemukan)
     */
    public function claimTicket(int $ticketId, int $devId)
    {
        return DB::transaction(function () use ($ticketId, $devId) {
            $ticket = DB::table($this->table)
                ->where('id', $ticketId)
                ->where('isDeleted', 0)
                ->whereNull('devID') // tambah guard: belum ter-assign
                ->lockForUpdate()
                ->first();

            if (!$ticket || strcasecmp((string)$ticket->status, self::STATUS_OPEN) !== 0) {
                return null;
            }

            DB::table($this->table)
                ->where('id', $ticketId)
                ->update([
                    'devID' => $devId,
                    'status' => self::STATUS_IN_PROGRESS,
                    'updated_at' => now(), //set updated_at
                ]);

            return DB::table($this->table)->where('id', $ticketId)->first();
        });
    }
}
