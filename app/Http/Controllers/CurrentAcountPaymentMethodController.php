<?php

namespace App\Http\Controllers;

use App\CurrentAcountPaymentMethod;
use Illuminate\Http\Request;

class CurrentAcountPaymentMethodController extends Controller
{
    function index() {
        $current_acount_payment_methods = CurrentAcountPaymentMethod::all();
        return response()->json(['current_acount_payment_methods' => $current_acount_payment_methods], 200);
    }
}
