<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{
    protected $SupportTicket;

    public function __construct()
    {
        $this->SupportTicket = new SupportTicket();
    }

    public function index()
    {
        $ticketsTable = 'devTickets';
        if (!Schema::hasTable($ticketsTable)) {
            abort(500, "Tabel $ticketsTable tidak ditemukan.");
        }

        $cols = collect(Schema::getColumnListing($ticketsTable));
        $find = function (array $cands) use ($cols) {
            foreach ($cands as $c) if ($cols->contains($c)) return $c;
            return null;
        };

        // Kandidat nama kolom di tickets
        $supportFk  = $find(['support_id','supportID','id_support','idSupport','idsupport','supportId']);
        $supportTxt = $find(['support','support_name','supportUser']);

        $devFk  = $find(['developer_id','dev_id','devID','id_developer','idDeveloper','idDev','developerId']);
        $devTxt = $find(['developer','dev','developer_name']);

        $roleFk  = $find(['role_id','roleID','id_role','idRole','toRole','to_role']);
        $roleTxt = $find(['role','toRole','to_role','role_name']);

        // Deteksi tabel role di DB
        $roleTable = null; $roleNameCol = null;
        foreach (['roles','devRoles','role','devroles'] as $tbl) {
            if (Schema::hasTable($tbl)) {
                $roleTable = $tbl;
                foreach (['name','roleName','namaRole'] as $col) {
                    if (Schema::hasColumn($tbl,$col)) { $roleNameCol = $col; break; }
                }
                break;
            }
        }

        $q = DB::table("$ticketsTable as t")->select(['t.id','t.title','t.status','t.created_at']);

        // Support
        if ($supportFk && Schema::hasTable('users') && Schema::hasColumn('users','id')) {
            $q->leftJoin('users as s',"t.$supportFk",'=','s.id')
              ->addSelect(DB::raw("COALESCE(NULLIF(s.name,''), NULLIF(s.username,''), ".($supportTxt ? "NULLIF(t.$supportTxt,'')" : "NULL").") as support"));
        } elseif ($supportTxt) {
            $q->addSelect(DB::raw("NULLIF(t.$supportTxt,'') as support"));
        } else {
            $q->addSelect(DB::raw("NULL as support"));
        }

        // Developer
        if ($devFk && Schema::hasTable('users') && Schema::hasColumn('users','id')) {
            $q->leftJoin('users as d',"t.$devFk",'=','d.id')
              ->addSelect(DB::raw("COALESCE(NULLIF(d.name,''), NULLIF(d.username,''), ".($devTxt ? "NULLIF(t.$devTxt,'')" : "NULL").") as dev"));
        } elseif ($devTxt) {
            $q->addSelect(DB::raw("NULLIF(t.$devTxt,'') as dev"));
        } else {
            $q->addSelect(DB::raw("NULL as dev"));
        }

        // Role
        if ($roleTable && $roleNameCol && $roleFk && $roleFk !== $roleTxt) {
            $q->leftJoin("$roleTable as r","t.$roleFk",'=','r.id')
              ->addSelect(DB::raw("NULLIF(r.$roleNameCol,'') as role"));
        } elseif ($roleTxt) {
            $q->addSelect(DB::raw("NULLIF(t.$roleTxt,'') as role"));
        } else {
            $q->addSelect(DB::raw("NULL as role"));
        }

        $alldata = $q->orderBy('t.id')->get();
        return view('SupportTicket.data', compact('alldata'));
    }
    
    public function hapusData(Request $request, $id)
    {
        try {
            $this->SupportTicket->deleteData($id);
            return response()->json(['success' => true, 'message' => 'Ticket berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ticket tidak dapat dihapus karena berelasi dengan data lainnya!'
            ]);
        }
    }
}
