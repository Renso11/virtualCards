<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailAlerte extends Mailable
{ use Queueable, SerializesModels;

    public $data; 

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $messages = $this->data['messages'];
        $objet = $this->data['objet'];
        $from = $this->data['from'];
        return $this->subject($objet)->from($from)->view('mail.alerte',compact('messages','objet'));
    }
}
