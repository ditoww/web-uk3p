<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClientTicket;

class ClientTicketController extends Controller
{
    public function index()
    {
        $data = [
            'alldata' => ClientTicket::where('isDeleted', 0)->get(),
        ];
        return view('ClientTicket.data', $data);
    }
    
    public function hapusData(Request $request, $id)
    {
        try {
            $ticket = ClientTicket::findOrFail($id);
            $ticket->isDeleted = 1;
            $ticket->save();
            return response()->json(['success' => true, 'message' => 'Ticket berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Ticket tidak dapat dihapus karena berelasi dengan data lainnya!'
            ]);
        }
    }
}