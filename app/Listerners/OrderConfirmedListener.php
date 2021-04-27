<?php

namespace App\Listerners;

use App\Buyer;
use App\Events\OrderConfirmed;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Http\Controllers\Helpers\TwilioHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderConfirmedListener
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
     * @param  OrderConfirmed  $event
     * @return void
     */
    public function handle(OrderConfirmed $event)
    {
        $title = 'Confirmamos tu pedido';
        $message = OrderNotificationHelper::getConfirmedMessage($event->order);
        TwilioHelper::sendNotification($event->order->buyer_id, $title, $message);
    }
}
