<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VenteVirtuelle extends Mailable
{
    use Queueable, SerializesModels;

    public $data; 

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Array $data)
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
        $data = $this->data;     
        return $this->subject("Votre carte virtuelle est disponible")->from('noreply-bcv@bestcash.me')->view('mail.venteVirtuelle',compact('data'));

    }
}
