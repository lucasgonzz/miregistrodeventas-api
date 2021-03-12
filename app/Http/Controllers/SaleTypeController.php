<?php

namespace App\Http\Controllers;

use App\SaleType;
use Illuminate\Http\Request;

class SaleTypeController extends Controller
{
    function index() {
    	$sale_types = SaleType::where('user_id', $this->userId())
    							->get();
    	return response()->json(['sale_types' => $sale_types], 200);
    }
}
