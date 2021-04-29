<?php

namespace App\Http\Controllers\Helpers;

use App\Buyer;
use App\Cart;
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

    static function deleteOrderCart($order) {
        $cart = Cart::where('order_id', $order->id)
                    ->first();
        if ($cart) {
            $cart->articles()->detach();
            $cart->delete();
        }
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

    static function getCanceledDescription($articulos_faltantes, $order) {
        if (count($articulos_faltantes) >= 1) {
            $count = 1;
            $message = 'No hay stock disponible para ';
            foreach ($articulos_faltantes as $article) {
                if ($count > 1) {
                    $message .= ', ni para ';
                }
                $message .= Self::getVariantName($article);
                $count++;
            }
            $message .= '.';
            return $message;
        } else {
            return $order['description'];
        }
    }

    static function getVariantName($article) {
        if ($article['pivot']['variant_id']) {
            foreach ($article['variants'] as $variant) {
                if ($variant['id'] == $article['pivot']['variant_id']) {
                    return $article['name'] . ' ' . $variant['description'];
                }
            }
        } 
        return $article['name'];
    }
}