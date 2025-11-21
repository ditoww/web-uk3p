<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompleteMail extends Mailable
{
    use SerializesModels;

    public $name;
    public $ticketName;
    public $ticketLink;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $ticketName, $ticketLink)
    {
        $this->name = $name;
        $this->ticketName = $ticketName;
        $this->ticketLink = $ticketLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tiket Selesai')->view('auth.mails.selesai');
    }
}
