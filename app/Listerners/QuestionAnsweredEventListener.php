<?php

namespace App\Listerners;

use App\Events\QuestionAnsweredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class QuestionAnsweredEventListener
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
     * @param  QuestionAnsweredEvent  $event
     * @return void
     */
    public function handle(QuestionAnsweredEvent $event)
    {
        //
    }
}
