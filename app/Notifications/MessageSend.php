<?php

namespace App\Notifications;

use App\Http\Controllers\Helpers\ImageHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
    public function __construct($message, $for_commerce = false, $title = null, $url = null)
    {
        $this->message = $message;
        $this->for_commerce = $for_commerce;
        $this->title = $title;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($this->for_commerce) {
            return ['broadcast'];
        } 
        return ['broadcast', 'mail'];
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

    public function toMail($notifiable)
    {
        Log::info('Enviando correo a '.$notifiable->email);
        return (new MailMessage)
                    ->from(Auth()->user()->email, Auth()->user()->company_name)
                    ->subject($this->title)
                    ->markdown('emails.message-send', [
                        'commerce'  => Auth()->user(),
                        'message'   => $this->message->text,
                        'logo_url'  => ImageHelper::image(Auth()->user()),
                    ]);
        // if (!is_null($this->url)) {
        //     $mail_message->action('Ver producto en la tienda', $this->url);
        // }
        // return (new MailMessage)
        //             // ->theme('custom')
        //             ->greeting('Hola '.$notifiable->name)
        //             ->from(Auth()->user()->email, Auth()->user()->company_name)
        //             ->subject($this->title)
        //             ->line($this->message->text)
        //             ->line('¡Muchas gracias por usar nuestros servicios!');
    }
}
