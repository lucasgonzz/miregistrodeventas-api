<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MessageSend extends Notification
{
    use Queueable;
    private $message;
    private $for_commerce;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message, $for_commerce = false)
    {
        $this->message = $message;
        $this->for_commerce = $for_commerce;
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
        if (!$this->for_commerce) {
            return 'message.from_commerce.'.$this->message->buyer_id;
        } else {
            return 'message.from_buyer.'.$this->message->user_id;
        }
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->message,
        ]);
    }
}
