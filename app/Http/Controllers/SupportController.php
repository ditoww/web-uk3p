<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ClientTicket;

use Kreait\Firebase\Factory;

class SupportController extends Controller
{
    public function support()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // Mengambil semua tiket yang terkait dengan support
        $tickets = DB::table('clientTickets')
        ->leftJoin('users AS support', 'clientTickets.supportID', '=', 'support.id')
        ->join('clients AS client', 'clientTickets.userID', '=', 'client.id')
        ->where(function ($query) use ($userId) { $query->whereNull('clientTickets.supportID')->orWhere('clientTickets.supportID', $userId); })
        ->where('clientTickets.isDeleted', '=', 0)
        ->whereIn('clientTickets.status', ['SEDANG DIPROSES', 'BELUM DIPROSES', 'SELESAI'])
        ->select(
            'clientTickets.id',
            'clientTickets.title',
            'clientTickets.status',
            'clientTickets.created_at',
            'clientTickets.supportID',
            //'support.name AS support_name',
            //'support.profile_picture AS support_avatar',
            //'support.email AS support_email',
            'client.name AS client_name',
            'client.email AS client_email',
            'client.profile_picture AS client_avatar',
            DB::raw('COALESCE((SELECT MAX(created_at) FROM clientChats WHERE clientChats.ticketID = clientTickets.id), clientTickets.created_at) as latest_chat_time')
        )
        ->orderByRaw("CASE WHEN clientTickets.status = 'SEDANG DIPROSES' THEN 0 WHEN clientTickets.status = 'BELUM DIPROSES' THEN 1 ELSE 2 END ASC")
        ->orderBy('latest_chat_time', 'desc')
        ->get();

        return view('Support.support', compact('tickets', 'user'));
    }

    public function takeTicket(Request $request, $id) {
        $ticket = ClientTicket::findOrFail($id);

        if ($ticket->supportID || $ticket->status === "DITUTUP") {
            //return back()->withErrors(['Tiket sudah diambil!']);
            return response()->json([
                "result" => "failed"
            ], 409);
        }
        
        $ticket->status = "SEDANG DIPROSES";
        $ticket->supportID = Auth::id();
        $ticket->save();
        $this->changeToProgressToFirebase($ticket);
        //return back();
        return response()->json([
            "result" => "success",
            "user_id" => Auth::id(),
            "id" => $id
        ]);
    }

    private function changeToProgressToFirebase(ClientTicket $ticket) {
        $factory = (new Factory)->withServiceAccount(base_path(config('firebase.credentials.file')))->withDatabaseUri(config('firebase.credentials.url')); 
        $db = $factory->createDatabase();

        $db->getReference("tickets/status/progress/{$ticket->id}")->set([
                'id' => $ticket->id,
                'user_id' => $ticket->supportID,
                'status' => 'SEDANG DIPROSES'
            ]);

        $db->getReference("tickets/status/progress/{$ticket->id}")->remove();
    }
} 