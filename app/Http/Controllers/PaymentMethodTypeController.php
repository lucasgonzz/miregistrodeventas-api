<?php

namespace App\Http\Controllers;

use App\PaymentMethodType;
use Illuminate\Http\Request;

class PaymentMethodTypeController extends Controller
{
    function index() {
        $models = PaymentMethodType::all();
        return response()->json(['payment_method_types' => $models], 200);
    }
}
