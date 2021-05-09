<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\Customer as LocalCustomer;
use App\Http\Controllers\CustomerController;
use App\Notifications\PaymentUpdated;
use App\Payment;
use Illuminate\Http\Request;
use MercadoPago\Card;
use MercadoPago\Customer;
use MercadoPago\Payer;
use MercadoPago\Payment as MercadoPagoPayment;
use MercadoPago\SDK;

class PaymentController extends Controller
{
    function procesarPago($order) {
        $payment = Payment::where('order_id', $order->id)
                            ->first();
        SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));

        if (!is_null($payment->card_id)) {
            $mp_payment = new MercadoPagoPayment();
            $mp_payment->transaction_amount = (float)$payment->transaction_amount;
            $mp_payment->token              = $payment->token;
            $mp_payment->installments       = (int)$payment->installments;
            $mp_payment->description        = $payment->description;
            $mp_payment->payer = [
                'type' => 'customer',
                'id'   => $payment->customer_id,
            ];
            $mp_payment->save();
        } else {
            $mp_payment = new MercadoPagoPayment();
            $mp_payment->transaction_amount = (float)$payment->transaction_amount;
            $mp_payment->token              = $payment->token;
            $mp_payment->installments       = (int)$payment->installments;
            $mp_payment->description        = $payment->description;
            $mp_payment->payment_method_id  = $payment->payment_method_id;
            $mp_payment->issuer_id          = (int)$payment->issuer;
            $payer = new Payer();
            $payer->email           = $payment->email;
            $payer->identification  = array(
                "type" => $payment->doc_type,
                "number" => $payment->doc_number
            );
            $mp_payment->payer = $payer;
            $mp_payment->save();
        }

        $payment->payment_id    = $mp_payment->id;
        $payment->status        = $mp_payment->status;
        $payment->status_detail = $mp_payment->status_detail;
        $payment->save();
        $this->saveCustomerAndCard($payment);
    }

    function saveCustomerAndCard($payment) {
        if ($payment->status == 'approved') {
            SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));
            $filters = ['email' => $payment->email];
            $customers = Customer::search($filters);
            if ($customers->total >= 1) {
                $customer_id = $customers[0]->id;
            } else {
                $customer = new Customer();
                $customer->email = $payment->email;
                $customer->save();
                $customer_id = $customer->id;
            }
            if ($customer_id) {
                $card = new Card();
                $card->token = $payment->token;
                $card->customer_id = $customer_id;
                $card->save();
            }
        }
    }

    function customer($payment_id) {
        $payment = Payment::find($payment_id);
        if ($payment->status == 'approved') {
            SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));
            $filters = ['email' => $payment->email];
            $customers = Customer::search($filters);
            if ($customers->total >= 1) {
                dd($customers[0]);
                $customer_id = $customers[0]->id;
            } else {
                $customer = new Customer();
                $customer->email = $payment->email;
                $customer->save();
                $customer_id = $customer->id;
            }
            if ($customer_id) {
                $card = new Card();
                $card->token = $payment->token;
                $card->customer_id = $customer_id;
                $card->save();
            }
        }
    }
    
    function refresh() {
        $payments = Payment::all();
        foreach ($payments as $payment) {
            $payment->delete();
        }
        $orders = \App\Order::all();
        foreach ($orders as $order) {
            $order->articles()->detach();
            $order->delete();
        }
        $carts = \App\Cart::all();
        foreach ($carts as $cart) {
            $cart->articles()->detach();
            $cart->delete();
        }
    }

    function notification(Request $request) {
        SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));
        if ($request->topic == 'payment') {
            $payment = Payment::where('payment_id', $request->id);
            $payment_mp = MercadoPagoPayment::find_by_id($request->id);
            if (!is_null($payment) && !is_null($payment_mp)) {
                $payment->status = $payment_mp->status;
                $payment->status_detail = $payment_mp->status_detail;
                $payment->updated = 1;
                $payment->save();
                $this->saveCustomerAndCard($payment);
                $order = Order::find($payment->order_id);
                $user = User::find($order->user_id);
                $user->notify(new PaymentUpdated($order));

                OrderHelper::checkPaymentMethodError($order, $buyer); 
            }
        }
        return response(null, 200);
    }
}
