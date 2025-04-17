<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email_data;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct($email_data)
    {
        $this->email_data = $email_data;
    }

    /**
     * Construir el mensaje.
     */
    public function build()     
    {
        return $this->from('testwalletelian@gmail.com', 'Test Wallet')
            ->subject($this->email_data['subject'])
            ->view('emails.generic_email', 
            [   
                'user_name'=> $this->email_data['name'],
                'token'=>$this->email_data['token'],
            ]
        
        
        );

    }
}