<?php

namespace App\Http\Controllers;

use App\ProviderOrderStatus;
use Illuminate\Http\Request;

class ProviderOrderStatusController extends Controller
{
    function index() {
        $models = ProviderOrderStatus::all();
        return response()->json(['models' => $models], 200);
    }
}
