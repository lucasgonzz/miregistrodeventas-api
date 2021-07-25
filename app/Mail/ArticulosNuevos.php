<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArticulosNuevos extends Mailable
{
    use Queueable, SerializesModels;

    public $articles;
    public $buyer;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($articles, $buyer, $user)
    {
        $this->articles = $articles;
        $this->buyer = $buyer;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(['address' => 'novedades3s@kioscoverde.com', 'name' => 'Kiosco VerDe'])
                    ->markdown('mails.asd');
    }
}
