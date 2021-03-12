<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\Sale;
use App\Events\OrderEvent;
use App\Http\Controllers\Helpers\Sale\SaleHelper;

class OrderController extends Controller
{
    function unconfirmed() {
        $orders = Order::where('user_id', $this->userId())
                        ->where('status', 'unconfirmed')
                        ->with('articles.images')
                        ->with('buyer')
                        ->get();
        return response()->json(['orders' => $orders], 200);
    }
    
    function confirmedFinished() {
        $orders = Order::where('user_id', $this->userId())
                        ->where('status', 'confirmed')
                        ->orWhere('status', 'finished')
                        ->with('articles.images')
                        ->with('buyer')
                        ->get();
        return response()->json(['orders' => $orders], 200);
    }

    function confirm($order_id) {
        $order = Order::find($order_id);
        $order->status = 'confirmed';
        $order->save();
        if ($order->payment_method == 'tarjeta') {
            $payment_controller = new PaymentController();
            $payment_controller->procesarPago($order->payment_id);
        }
        broadcast(new OrderEvent($order))->toOthers();
        return response(null, 200);
    }

    function cancel($order_id) {
        $order = Order::find($order_id);
        $order->status = 'canceled';
        $order->save();
        broadcast(new OrderEvent($order))->toOthers();
        return response(null, 200);
    }

    function finish($order_id) {
        $order = Order::find($order_id);
        $order->status = 'finished';
        $order->save();
        broadcast(new OrderEvent($order))->toOthers();
        return response(null, 200);
    }

    function deliver($order_id) {
        $order = Order::where('id', $order_id)
                        ->with('articles')
                        ->first();
        $order->status = 'delivered';
        $order->save();
        broadcast(new OrderEvent($order))->toOthers();
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
                    // ->with('buyer')
                    ->with('articles')
                    ->with('impressions')
                    ->with('specialPrice')
                    ->first();
        return $sale;
    }
}
