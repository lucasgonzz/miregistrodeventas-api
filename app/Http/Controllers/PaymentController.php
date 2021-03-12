<?php

namespace App\Http\Controllers;

use App\Payment;
use Illuminate\Http\Request;
use MercadoPago\Payer;
use MercadoPago\Payment as MercadoPagoPayment;
use MercadoPago\SDK;

class PaymentController extends Controller
{
    function procesarPago($payment_id) {
        $payment = Payment::find($payment_id);
        SDK::setAccessToken("TEST-3668585670354328-100112-a353cb99b53860f22fdf7e7b87c4fd8b-163250661");

        $mp_payment = new MercadoPagoPayment();
        $mp_payment->transaction_amount = (float)$payment->transaction_amount;
        $mp_payment->token = $payment->token;
        $mp_payment->description = $payment->description;
        $mp_payment->installments = (int)$payment->installments;
        $mp_payment->payment_method_id = $payment->payment_method_id;
        $mp_payment->issuer_id = (int)$payment->issuer;

        $payer = new Payer();
        $payer->email = $payment->email;
        $payer->identification = array(
            "type" => $payment->doc_type,
            "number" => $payment->doc_number
        );
        $mp_payment->payer = $payer;

        $mp_payment->save();

        $payment->status = $mp_payment->status;
        $payment->status_details = $mp_payment->status_details;
        $payment->save();
    }
}
