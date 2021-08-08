<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDelivered extends Notification
{
    use Queueable;

    private $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
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
        return ['broadcast'];
        // return ['database', 'broadcast', WhatsAppChannel::class];
    }

    public function broadcastOn()
    {
        return 'order.'.$this->order->buyer_id;
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'order' => $this->order,
        ]);
    }

    public function toWhatsApp($notifiable)
    {
        return (new WhatsAppMessage)
            ->content(OrderNotificationHelper::getDeliveredMessage($this->order));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message'  => OrderNotificationHelper::getDeliveredMessage($this->order),
        ];
    }
}
