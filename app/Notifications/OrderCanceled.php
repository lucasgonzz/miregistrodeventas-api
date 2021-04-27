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

class OrderCanceled extends Notification implements ShouldQueue
{
    use Queueable;

    private $order;
    private $description;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, $description)
    {
        $this->order = $order;
        $this->description = $description;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast', WhatsAppChannel::class];
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
        $buyer_name = $notifiable->name;
        $message = "Hola {$buyer_name}. ".OrderNotificationHelper::getCanceledMessage($this->description);
        return (new WhatsAppMessage)
            ->content($message);
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
            'message'  => OrderNotificationHelper::getCanceledMessage($this->description),
        ];
    }
}
