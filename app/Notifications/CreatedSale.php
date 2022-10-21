<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CreatedSale extends Notification
{
    use Queueable;
    public $sale;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($sale)
    {
        $this->sale = $sale;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast'];
    }

    public function broadcastOn()
    {
        return 'created_sale.'.$this->sale->user_id;
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'sale_id' => $this->sale->id,
        ]);
    }
}
