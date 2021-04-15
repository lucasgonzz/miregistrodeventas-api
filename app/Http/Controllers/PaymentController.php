<?php

namespace App\Http\Controllers;

use App\Payment;
use Illuminate\Http\Request;
use MercadoPago\Payer;
use MercadoPago\Payment as MercadoPagoPayment;
use MercadoPago\SDK;

class PaymentController extends Controller
{
    function procesarPago($order) {
        $payment = Payment::where('order_id', $order->id)
                            ->first();
        SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));

        $mp_payment = new MercadoPagoPayment();
        $mp_payment->transaction_amount = (float)$payment->transaction_amount;
        $mp_payment->notification_url   = env('APP_URL').'/mercado-pago/notification';
        $mp_payment->token              = $payment->token;
        $mp_payment->description        = $payment->description;
        $mp_payment->installments       = (int)$payment->installments;
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

        $payment->payment_id    = $mp_payment->id;
        $payment->status        = $mp_payment->status;
        $payment->status_detail = 'Se proceso';
        $payment->save();
    }

    function notification(Request $request) {
        SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));
        if ($request->type == 'payment') {
            Payment::create([
                'payment_id' => $request->id,
                'description' => $request->data->id,
                'status' => $request->action,
            ]);
            // $payment = Payment::where('payment_id', $request->id);
            // $payment->status_detail = $payment->action;
            // $payment->save(); 
        }
        return response(null, 200);
    }
}
