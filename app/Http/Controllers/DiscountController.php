<?php

namespace App\Http\Controllers;

use App\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index() {
    	$discounts = Discount::where('user_id', $this->userId())
    							->get();
    	return response()->json(['discounts' => $discounts], 200);
    }

    function update(Request $request) {
    	$discount = Discount::find($request->id);
    	$discount->name = ucfirst($request->name);
    	$discount->percentage = $request->percentage;
    	$discount->save();
    	return response()->json(['discount' => $discount], 200);
    }

    function store(Request $request) {
    	$discount = Discount::create([
    		'name' => ucfirst($request->name),
    		'percentage' => $request->percentage,
    		'user_id' => $this->userId(),
    	]);
    	return response()->json(['discount' => $discount], 201);
    }
}
