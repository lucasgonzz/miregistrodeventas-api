<?php

namespace App\Listerners;

use App\Events\OrderCanceled;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Http\Controllers\Helpers\TwilioHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderCanceledListener
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
     * @param  OrderCanceled  $event
     * @return void
     */
    public function handle(OrderCanceled $event)
    {
        $message = OrderNotificationHelper::getCanceledMessage($event->description);
        $title = 'Cancelamos tu pedidio';
        TwilioHelper::sendNotification($event->order->buyer_id, $title, $message);
    }
}
