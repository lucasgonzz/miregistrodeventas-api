<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Buyer;
use App\Cart;
use App\Events\OrderConfirmed as OrderConfirmedEvent;
use App\Events\OrderFinished as OrderFinishedEvent;
use App\Events\PaymentError as PaymentErrorEvent;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Http\Controllers\PaymentController;
use App\Listerners\OrderConfirmedListene;
use App\Notifications\OrderConfirmed as OrderConfirmedNotification;
use App\Notifications\OrderFinished as OrderFinishedNotification;
use App\Notifications\PaymentError as PaymentErrorNotification;
use App\Variant;


class OrderHelper {
	static function setArticlesKey($orders) {
		foreach ($orders as $order) {
			$order->articles = ArticleHelper::setArticlesKey($order->articles);
		}
		return $orders;
	}

    static function deleteCartOrder($order) {
        $cart = Cart::where('order_id', $order->id)
                    ->first();
        if ($cart) {
            $cart->articles()->detach();
            $cart->delete();
        }
    }

    static function procesarPago($order) {
        if ($order->payment_method == 'tarjeta') {
            $payment_controller = new PaymentController();
            $payment_controller->procesarPago($order);
        }
    }

    static function discountArticleStock($articles) {
        foreach ($articles as $article) {
            $article_ = Article::find($article->id);
            if (!is_null($article->pivot->variant_id)) {
                $variant = Variant::find($article->pivot->variant_id);
                $stock_resultante = $variant->stock - $article->pivot->amount;
                if ($stock_resultante > 0) {
                    $variant->stock = $stock_resultante;
                } else {
                    $variant->stock = 0;
                }
                // $variant->description = 'hola';
                $variant->save();
            } else if (!is_null($article_->stock)) {
                $stock_resultante = $article_->stock - $article->pivot->amount;
                if ($stock_resultante > 0) {
                    $article_->stock = $stock_resultante;
                } else {
                    $article_->stock = 0;
                }
                $article_->timestamps = false;
                $article_->save();
            }
        }

    }

    static function sendOrderConfrimedNotification($order) {
        $buyer = Buyer::find($order->buyer_id);
        $buyer->notify(new OrderConfirmedNotification($order));
        event(new OrderConfirmedEvent($order));
        Self::checkPaymentMethodError($order, $buyer);
    }

    static function sendOrderFinishedNotification($order) {
        $buyer = Buyer::find($order->buyer_id);
        $buyer->notify(new OrderFinishedNotification($order));
        event(new OrderFinishedEvent($order));
    }

    static function checkPaymentMethodError($order, $buyer) {
        if ($order->payment_method == 'tarjeta' && $order->payment->status != '') {
            $check_payment_status = OrderNotificationHelper::checkPaymentStatus($order);
            if ($check_payment_status) {
                $buyer->notify(new PaymentSuccessNotification($order));
                event(new PaymentSuccessEvent($order));
            } else {
                $buyer->notify(new PaymentErrorNotification($order));
                event(new PaymentErrorEvent($order));
            }
        }
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