<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DevTicket;
use Illuminate\Http\Request;

class DevTicketController extends Controller
{
    public function get_single_datas($id)
    {
        $ticket = dev_ticket_model()->get_single_data($id);
        return success_response($ticket);
    }
}
