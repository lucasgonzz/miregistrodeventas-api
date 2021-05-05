<?php

namespace App\Listerners;

use App\Events\PaymentError;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Http\Controllers\Helpers\TwilioHelper;

class PaymentErrorListener
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
     * @param  PaymentError  $event
     * @return void
     */
    public function handle(PaymentError $event)
    {
        $title = 'Hubo un error con tu pago';
        $message = OrderNotificationHelper::checkPaymentMethod($event->order)['message'];
        TwilioHelper::sendNotification($event->order->buyer_id, $title, $message);
    }
}
