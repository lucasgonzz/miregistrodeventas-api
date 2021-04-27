<?php

namespace App\Listerners;

use App\Events\OrderFinished;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Http\Controllers\Helpers\TwilioHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderFinishedListener
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
     * @param  OrderFinished  $event
     * @return void
     */
    public function handle(OrderFinished $event)
    {
        $title = 'Tu pedido esta listo';
        $message = OrderNotificationHelper::getFinishedMessage($event->order);
        TwilioHelper::sendNotification($event->order->buyer_id, $title, $message);
    }
}
