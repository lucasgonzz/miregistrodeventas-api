<?php

namespace App\Listerners;

use App\Events\OrderDelivered;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Http\Controllers\Helpers\TwilioHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderDeliveredListener
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
     * @param  OrderDelivered  $event
     * @return void
     */
    public function handle(OrderDelivered $event)
    {
        $title = '¡Muchas gracias por tu compra!';
        $message = OrderNotificationHelper::getDeliveredMessage($event->order);
        TwilioHelper::sendNotification($event->order->buyer_id, $title, $message);
    }
}
