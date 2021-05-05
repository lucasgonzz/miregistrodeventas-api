<?php

namespace App\Listerners;

use App\Events\PaymentSuccess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentSuccessListener
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
     * @param  PaymentSuccess  $event
     * @return void
     */
    public function handle(PaymentSuccess $event)
    {
        $title = 'Se acredito tu pago';
        $message = OrderNotificationHelper::checkPaymentMethod($event->order)['message'];
        TwilioHelper::sendNotification($event->order->buyer_id, $title, $message);
    }
}
