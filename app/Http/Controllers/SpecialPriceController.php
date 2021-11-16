<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SpecialPrice;

class SpecialPriceController extends Controller
{
    public function index() {
    	$special_prices = SpecialPrice::where('user_id', $this->userId())
                            ->where('status', 'active')
    						->get();
        return response()->json(['special_prices' => $special_prices], 200);
    }

    public function store(Request $request) {
    	$special_price = SpecialPrice::create([
    		'name' 	  => ucwords($request->name),
    		'user_id' => $this->userId()
    	]);
        return response()->json(['special_price' => $special_price], 201);
    }

    public function delete($id) {
    	$special_price = SpecialPrice::find($id);
    	$special_price->status = 'inactive';
        $special_price->save();
        return response(null, 200);
    }
}
