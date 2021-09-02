<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $buyer;
    public $commerce;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($buyer, $commerce)
    {
        $this->buyer = $buyer;
        $this->commerce = $commerce;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pedido Confirmado')
                    ->markdown('emails.orders.confirmed');
    }
}
