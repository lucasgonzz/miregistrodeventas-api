<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\OrderConfirmed' => [
            'App\Listerners\OrderConfirmedListener',
        ],
        'App\Events\OrderCanceled' => [
            'App\Listerners\OrderCanceledListener',
        ],
        'App\Events\OrderFinished' => [
            'App\Listerners\OrderFinishedListener',
        ],
        'App\Events\OrderDelivered' => [
            'App\Listerners\OrderDeliveredListener',
        ],
        'App\Events\QuestionAnswered' => [
            'App\Listerners\QuestionAnsweredListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
