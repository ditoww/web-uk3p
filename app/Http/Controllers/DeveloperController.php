<?php

namespace App\Http\Controllers;

use App\Models\supportticket; // Pastikan model ini sudah di-import
use App\Models\DevChat;
use App\Models\DevRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeveloperController extends Controller
{
    public function index()
    {

        $userId = Auth::id(); // Sesuaikan dengan Auth::id() jika diperlukan

        $user = DB::table('users')->where('id', $userId)->first();

        // Ambil tiket open + data support & role (tanpa pakai method allWithDevRole)
        $tickets = SupportTicket::query()
            ->leftJoin('users as support', 'devTickets.supportID', '=', 'support.id')
            ->leftJoin('devRoles', 'devTickets.roleID', '=', 'devRoles.id')
            ->where('devTickets.isDeleted', 0)
            ->where('devTickets.status', SupportTicket::STATUS_OPEN) // ganti: pakai konstanta
            ->select(
                'devTickets.id',
                'devTickets.title',
                'devTickets.status',
                'devTickets.created_at',
                'devTickets.updated_at',
                'devTickets.roleID',
                'support.profile_picture AS user_avatar',
                'devRoles.roleName',
                'support.name as support_name',
                'devTickets.priority',
                DB::raw('(SELECT response FROM devChats WHERE devChats.ticket_id = devTickets.id ORDER BY devChats.created_at ASC LIMIT 1) as first_response')
            )
            // Urutkan berdasarkan priority terendah terlebih dahulu, NULL diletakkan terakhir,
            // lalu stabilkan urutan dengan created_at agar konsisten untuk priority yang sama
            ->orderByRaw('CASE WHEN devTickets.priority IS NULL THEN 1 ELSE 0 END, devTickets.priority ASC')
            ->orderBy('devTickets.created_at', 'ASC')
            ->get();
        
        $roles = DevRole::where('isDeleted', 0)->get();

        // Mengirimkan data tiket yang sudah di-join ke view
        return view('Developer.developer', compact('tickets', 'user', 'roles'));
    }
    public function detail($id)
    {
        $userId = Auth::id(); // Sesuaikan dengan Auth::id() jika diperlukan

        $user = DB::table('users')->where('id', $userId)->first();

        // Koleksi tiket open + nama support & role
        $tickets = SupportTicket::query()
            ->leftJoin('users as support', 'devTickets.supportID', '=', 'support.id')
            ->leftJoin('devRoles', 'devTickets.roleID', '=', 'devRoles.id')
            ->where('devTickets.isDeleted', 0)
            ->where('devTickets.status', SupportTicket::STATUS_OPEN) // ganti: pakai konstanta
            ->select(
                'devTickets.id',
                'devTickets.title',
                'devTickets.status',
                'devTickets.created_at',
                'devTickets.updated_at',
                'support.profile_picture AS user_avatar',
                'devRoles.roleName',
                'support.name as support_name',
                'devTickets.priority',
                DB::raw('(SELECT response FROM devChats WHERE devChats.ticket_id = devTickets.id ORDER BY devChats.created_at ASC LIMIT 1) as first_response')
            )
            ->orderByRaw('CASE WHEN devTickets.priority IS NULL THEN 1 ELSE 0 END, devTickets.priority ASC')
            ->orderBy('devTickets.created_at', 'ASC')
            ->get();

        // Cari ticket yang sesuai id dari koleksi yang sudah ada
        $ticket = $tickets->firstWhere('id', $id);

        if (!$ticket) {
            abort(404);
        }

        return view('Developer.ticket_detail', compact('ticket', 'user'));
    }

    // Klaim tiket: assign devId (developer login) dan ubah status Open -> In Progress
    public function claim($id)
    {
        $devId = Auth::id();
        $supportTicket = new SupportTicket();
        $ticket = $supportTicket->claimTicket((int)$id, (int)$devId);

        if (!$ticket) {
            return redirect()->back()->with('error', 'Ticket tidak tersedia atau sudah diambil developer lain.');
        }

        // Tambahkan pesan system ke chat bahwa developer bergabung ke chat
        try {
            $devName = Auth::user()->name ?? 'Developer';
            DevChat::create([
                'ticket_id' => (int)$id,
                'sender' => 'system',
                'response' => $devName . ' telah mengambil tiket tersebut.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan mengganggu alur jika gagal menulis pesan system
        }

        // Redirect ke halaman chat developer dengan ticket yang baru diklaim
        return redirect()->route('Chatdev.chatdev', ['ticket' => $id])
                         ->with('success', 'Ticket berhasil diklaim dan status diubah menjadi In Progress.');
    }
}
