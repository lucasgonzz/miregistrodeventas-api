<?php

namespace App\Http\Controllers;

use App\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    function index() {
        $plans = Plan::with('features')
                        ->get();
        return response()->json(['plans' => $plans], 200);
    }
}
