<?php

namespace App\Http\Controllers;

use App\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index() {
    	$discounts = Discount::where('user_id', $this->userId())
                                ->with('client')
    							->get();
    	return response()->json(['discounts' => $discounts], 200);
    }

    function update(Request $request) {
    	$discount = Discount::find($request->id);
    	$discount->name = ucfirst($request->name);
    	$discount->percentage = $request->percentage;
    	$discount->save();
        $discounts = Discount::where('id', $discount->id)
                                ->with('client')
                                ->first();
    	return response()->json(['discount' => $discount], 200);
    }

    function store(Request $request) {
    	$discount = Discount::create([
    		'name'        => ucfirst($request->name),
            'percentage'  => $request->percentage,
    		'client_id'   => $request->client_id,
    		'user_id'     => $this->userId(),
    	]);
        $discount = Discount::where('id', $discount->id)
                                ->with('client')
                                ->first();
    	return response()->json(['discount' => $discount], 201);
    }
}
