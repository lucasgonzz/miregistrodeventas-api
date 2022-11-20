<?php

namespace App\Http\Controllers;

use App\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    function index() {
        $models = OrderStatus::all();
        return response()->json(['models' => $models], 200);
    }
}
