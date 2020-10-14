<?php

namespace App\Listerners;

use App\Events\OrderConfirmedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderConfirmedEventListener
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
     * @param  OrderConfirmedEvent  $event
     * @return void
     */
    public function handle(OrderConfirmedEvent $event)
    {
        //
    }
}
