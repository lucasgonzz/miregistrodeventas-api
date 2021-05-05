<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Http\Controllers\Helpers\OrderNotificationHelper;


class PaymentSuccess extends Notification
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn()
    {
        return 'payment.'.$this->order->buyer_id;
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message'  => OrderNotificationHelper::checkPaymentMethod($this->order)['message'],
        ];
    }
}
