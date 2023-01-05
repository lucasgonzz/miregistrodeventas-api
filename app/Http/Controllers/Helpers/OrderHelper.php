<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Buyer;
use App\Cart;
use App\Color;
use App\Events\OrderConfirmed as OrderConfirmedEvent;
use App\Events\OrderFinished as OrderFinishedEvent;
use App\Events\PaymentError as PaymentErrorEvent;
use App\Events\PaymentSuccess as PaymentSuccessEvent;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\MessageHelper;
use App\Http\Controllers\Helpers\OrderNotificationHelper;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Http\Controllers\PaymentController;
use App\Listerners\OrderConfirmedListene;
use App\Notifications\OrderConfirmed as OrderConfirmedNotification;
use App\Notifications\OrderFinished as OrderFinishedNotification;
use App\Notifications\PaymentError as PaymentErrorNotification;
use App\Notifications\PaymentSuccess as PaymentSuccessNotification;
use App\Sale;
use App\Size;
use App\Variant;


class OrderHelper {
    
    static function setArticlesColor($orders) {
        $colors = Color::all();
        foreach ($orders as $order) {
            foreach ($order->articles as $article) {
                if (isset($article->pivot) && $article->pivot->color_id) {
                    foreach ($colors as $color) {
                        if ($color->id == $article->pivot->color_id) {
                            $article->color = $color;
                        }
                    }
                } 
            }
        }
        return $orders;
    }

    static function setArticlesSize($orders) {
        $sizes = Size::all();
        foreach ($orders as $order) {
            foreach ($order->articles as $article) {
                if (isset($article->pivot) && $article->pivot->size_id) {
                    foreach ($sizes as $size) {
                        if ($size->id == $article->pivot->size_id) {
                            $article->size = $size;
                        }
                    }
                } 
            }
        }
        return $orders;
    }

    static function attachArticles($model, $articles) {
        $model->articles()->detach();
        foreach ($articles as $article) {
            $model->articles()->attach($article['id'], [
                'price' => $article['pivot']['price'],
                'amount' => $article['pivot']['amount'],
            ]);
        }
    }

    static function deleteCartOrder($order) {
        $cart = Cart::where('order_id', $order->id)
                    ->first();
        if ($cart) {
            $cart->articles()->detach();
            $cart->delete();
        }
    }

    static function updateCuponsStatus($order) {
        foreach ($order->cupons as $cupon) {
            $cupon->valid = 1;
            $cupon->order_id = null;
            $cupon->cart_id = null;
            $cupon->save();
        }
    }

    static function sendMail($model) {
        if ($model->order_status->name == 'Confirmado') {
            MessageHelper::sendOrderConfirmedMessage($model);
        } else if ($model->order_status->name == 'Terminado') {
            MessageHelper::sendOrderFinishedMessage($model);
        } else if ($model->order_status->name == 'Entregado') {
            MessageHelper::sendOrderDeliveredMessage($model);
        }
    }

    static function discountArticleStock($model) {
        if ($model->order_status->name == 'Sin confirmar') {
            foreach ($model->articles as $article) {
                $_article = Article::find($article->id);
                if (!is_null($_article->stock)) {
                    $stock_resultante = $_article->stock - $article->pivot->amount;
                    if ($stock_resultante > 0) {
                        $_article->stock = $stock_resultante;
                    } else {
                        $_article->stock = 0;
                    }
                    $_article->timestamps = false;
                    $_article->save();
                }
            }
        }
    }

    static function restartArticleStock($model) {
        foreach ($model->articles as $article) {
            $_article = Article::find($article->id);
            if (!is_null($_article->stock)) {
                $_article->stock += $article->pivot->amount;
                $_article->timestamps = false;
                $_article->save();
            }
        }
    }

    static function saveSale($order) {
        if ($order->order_status->name == 'Entregado') {
            $num_sale = SaleHelper::numSale(UserHelper::userId());
            $client_id = null;
            if (!is_null($order->buyer->comercio_city_client)) {
                $client_id = $order->buyer->comercio_city_client_id;
            }
            $sale = Sale::create([
                'user_id'               => UserHelper::userId(),
                'buyer_id'              => $order->buyer_id,
                'client_id'             => $client_id,
                'num_sale'              => $num_sale,
                'save_current_acount'   => 1,
                'order_id'              => $order->id,
                'employee_id'           => SaleHelper::getEmployeeId(),
            ]);
            SaleHelper::attachArticlesFromOrder($sale, $order->articles);
            if (!is_null($order->buyer->comercio_city_client)) {
                SaleHelper::attachCurrentAcountsAndCommissions($sale, $order->buyer->comercio_city_client_id, [], []);
            }
        }
    }

    static function sendOrderConfirmedNotification($order) {
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

    static function checkPaymentMethodError($order) {
        if ($order->payment_method == 'tarjeta' && $order->payment->status != '') {
            $check_payment_status = OrderNotificationHelper::checkPaymentStatus($order);
            if ($check_payment_status) {
                MessageHelper::sendPaymentSuccessMessage($order);
                // $order->buyer->notify(new PaymentSuccessNotification($order));
                // event(new PaymentSuccessEvent($order));
            } else {
                MessageHelper::sendPaymentErrorMessage($order);
                // $order->buyer->notify(new PaymentErrorNotification($order));
                // event(new PaymentErrorEvent($order));
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
            return StringHelper::onlyFirstWordUpperCase($order['cancel_description']);
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