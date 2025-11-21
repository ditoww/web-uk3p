<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use SerializesModels;

    public $name;
    public $ticketName;
    public $ticketLink;
    public $response;
    public $attCount;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $ticketName, $ticketLink, $response, $attCount)
    {
        $this->name = $name;
        $this->ticketName = $ticketName;
        $this->ticketLink = $ticketLink;
        $this->response = $response;
        $this->attCount = $attCount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pesan Baru')->view('auth.mails.notif');
    }
}
