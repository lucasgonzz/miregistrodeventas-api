<?php

namespace App\Listerners;

use App\Events\QuestionAnswered;
use App\Http\Controllers\Helpers\QuestionNotificationHelper;
use App\Http\Controllers\Helpers\TwilioHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class QuestionAnsweredListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QuestionAnswered  $event
     * @return void
     */
    public function handle(QuestionAnswered $event)
    {
        $title = 'Respondimos a tu pregunta';
        $message = QuestionNotificationHelper::getQuestionAnsweredMessage($event->question);
        TwilioHelper::sendNotification($event->question->buyer_id, $title, $message);
    }
}
