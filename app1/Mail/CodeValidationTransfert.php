<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodeValidationTransfert extends Mailable
{
    use Queueable, SerializesModels;
    
    public $message;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = $this->message;
        return $this->subject("Operation de transfert BCB Virtuelle")->from('noreply-bcv@bestcash.me')->view('mail.codeValidationTransfert',compact('message'));
    }
}
