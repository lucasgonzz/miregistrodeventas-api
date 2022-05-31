<?php

namespace App\Http\Controllers;

use App\OrderProductionStatus;
use Illuminate\Http\Request;

class OrderProductionStatusController extends Controller
{
    function index() {
        $order_production_statuses = OrderProductionStatus::all();
        return response()->json(['order_production_statuses' => $order_production_statuses], 200);
    }
}
