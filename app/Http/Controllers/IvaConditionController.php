<?php

namespace App\Http\Controllers;

use App\IvaCondition;
use Illuminate\Http\Request;

class IvaConditionController extends Controller
{
    function index() {
        $iva_conditions = IvaCondition::all();
        return response()->json(['iva_conditions' => $iva_conditions], 200);
    }
}
