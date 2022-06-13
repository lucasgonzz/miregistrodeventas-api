<?php

namespace App\Notifications;

use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderProductionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($order_production, $subject, $line) 
    {
        $this->order_production = $order_production;
        $this->subject = $subject;
        $this->line = $line;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $greeting = 'Hola '.$this->order_production->budget->client->name;
        $url = env('APP_URL').'/budgets/pdf/0/'.$this->order_production->budget->id;
        $user = UserHelper::getFullModel();
        return (new MailMessage)
                    ->from($user->email, $user->company_name)
                    ->subject($this->subject)
                    ->greeting($greeting)
                    ->line($this->line)
                    ->line('A continuación te dejamos el enlase para que puedas consultar tu presupuesto')
                    ->action('VER PDF', $url)
                    ->line('¡Gracias por utilizar nuestro servicios!');
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
            //
        ];
    }
}
