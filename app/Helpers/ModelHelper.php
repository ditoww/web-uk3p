<?php

use App\Models\DevTicket;

if (!function_exists('dev_ticket_model')) {
    function dev_ticket_model()
    {
        return new DevTicket();
    }
}
