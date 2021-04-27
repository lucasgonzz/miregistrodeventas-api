<?php

namespace App\Http\Controllers\Helpers;

use App\Buyer;
use App\Events\OrderConfirmed as OrderConfirmedEvent;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\PaymentController;
use App\Listerners\OrderConfirmedListene;
use App\Notifications\OrderConfirmed as OrderConfirmedNotification;


class OrderHelper {
	static function setArticlesKey($orders) {
		foreach ($orders as $order) {
			$order->articles = ArticleHelper::setArticlesKey($order->articles);
		}
		return $orders;
	}

    static function checkPaymentMethod($order) {
        if ($order->payment_method == 'tarjeta') {
            $payment_controller = new PaymentController();
            $payment_controller->procesarPago($order);
        }
    }

    static function sendOrderConfrimedNotification($order) {
        $buyer = Buyer::find($order->buyer_id);
        $buyer->notify(new OrderConfirmedNotification($order));
        event(new OrderConfirmedEvent($order));
    }
}