<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\Events\OrderCanceled as OrderCanceledEvent;
use App\Events\OrderDelivered as OrderDeliveredEvent;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\CartHelper;
use App\Http\Controllers\Helpers\OrderHelper;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Notifications\OrderCanceled as OrderCanceledNotification;
use App\Notifications\OrderDelivered as OrderDeliveredNotification;
use App\Order;
use App\Sale;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    function unconfirmed() {
        $orders = Order::where('user_id', $this->userId())
                        ->where('status', 'unconfirmed')
                        ->with('articles.images')
                        ->with('articles.variants')
                        ->with('buyer')
                        ->get();
        $orders = OrderHelper::setArticlesKey($orders);
        return response()->json(['orders' => $orders], 200);
    }
    
    function confirmedFinished() {
        $orders = Order::where('user_id', $this->userId())
                        ->where('status', 'confirmed')
                        ->orWhere('status', 'finished')
                        ->with('articles.images')
                        ->with('articles.variants')
                        ->with('buyer')
                        ->with('payment')
                        ->get();
        $orders = OrderHelper::setArticlesKey($orders);
        return response()->json(['orders' => $orders], 200);
    }

    function confirm($order_id) {
        $order = Order::find($order_id);
        $order->status = 'confirmed';
        $order->save();
        OrderHelper::procesarPago($order);
        OrderHelper::sendOrderConfrimedNotification($order);
        OrderHelper::discountArticleStock($order->articles);
        return response(null, 200);
    }

    function cancel(Request $request) {
        $order = Order::find($request->order['id']);
        $order->status = 'canceled';
        $order->save();
        $order->articles = ArticleHelper::setArticlesKey($order->articles);
        CartHelper::detachArticulosFaltantes($request->articulos_faltantes, $order);
        $buyer = Buyer::find($order->buyer_id);
        $message = OrderHelper::getCanceledDescription($request->articulos_faltantes, $request->order);
        $buyer->notify(new OrderCanceledNotification($order, $message));
        event(new OrderCanceledEvent($order, $message));
        return response(null, 200);
    }

    function finish($order_id) {
        $order = Order::find($order_id);
        $order->status = 'finished';
        $order->save();
        OrderHelper::deleteCartOrder($order);
        OrderHelper::sendOrderFinishedNotification($order);
        return response(null, 200);
    }

    function deliver($order_id) {
        $order = Order::where('id', $order_id)
                        ->with('articles')
                        ->first();
        $order->status = 'delivered';
        $order->save();

        // Notification
        $buyer = Buyer::find($order->buyer_id);
        $buyer->notify(new OrderDeliveredNotification($order));
        event(new OrderDeliveredEvent($order));

        $sale = $this->saveSale($order);
        return response()->json(['sale' => $sale], 201);
    }

    function saveSale($order) {
        $num_sale = SaleHelper::numSale($this->userId());
        $sale = Sale::create([
            'user_id' => $this->userId(),
            'buyer_id' => $order->buyer_id,
            'num_sale' => $num_sale
        ]);
        SaleHelper::attachArticlesFromOrder($sale, $order->articles);
        $sale = Sale::where('id', $sale->id)
                    ->with('client')
                    ->with('buyer')
                    ->with('articles')
                    ->with('impressions')
                    ->with('specialPrice')
                    ->with('discounts')
                    ->with('commissions')
                    ->first();
        return $sale;
    }
}
