<?php

namespace App\Notifications;

use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($budget)
    {
        $this->budget = $budget;
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
        $greeting = 'Hola '.$this->budget->client->name;
        $url = env('APP_URL').'/budgets/pdf/0/'.$this->budget->id;
        $user = UserHelper::getFullModel();
        return (new MailMessage)
                    ->from($user->email, $user->company_name)
                    ->subject('PRESUPUESTO')
                    ->greeting($greeting)
                    ->line('Te acercamos tu presupuesto mediante el siguiente documento PDF.')
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
