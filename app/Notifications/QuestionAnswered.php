<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppChannel;
use App\Http\Controllers\Helpers\QuestionNotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestionAnswered extends Notification
{
    use Queueable;

    private $question;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($question)
    {
        $this->question = $question;
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
        return 'question.'.$this->question->buyer_id;
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'question' => $this->question,
        ]);
    }

    public function toWhatsApp($notifiable)
    {
        $buyer_name = $notifiable->name;
        $message = 'Hola '.$buyer_name.'. '.QuestionNotificationHelper::getQuestionAnsweredMessage($this->question);
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
            'article_slug'  => $this->question->article->slug,
            // 'variant_id'  => $this->question->variant_id,
            'message'  => QuestionNotificationHelper::getQuestionAnsweredMessage($this->question),
        ];
    }
}
