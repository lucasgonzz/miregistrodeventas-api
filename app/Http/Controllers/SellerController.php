<?php

namespace App\Http\Controllers;

use App\Commissioner;
use App\CurrentAcount;
use App\Http\Controllers\Helpers\StringHelper;
use App\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index() {
    	$sellers = Seller::where('user_id', $this->userId())
    						->get();
    	return response()->json(['sellers' => $sellers], 200);
    }

    function store(Request $request) {
        $seller = Seller::create([
            'name' => StringHelper::onlyFirstWordUpperCase($request->name),
            'surname' => StringHelper::onlyFirstWordUpperCase($request->surname),
            'user_id' => $this->userId(),
            // 'percentage' => $request->percentage,
        ]);
        $commissioner = Commissioner::create([
            'seller_id' => $seller->id,
            'user_id' => $this->userId(),
        ]);
        $commissioner = Commissioner::where('id', $commissioner->id)
                                    ->with('seller')
                                    ->first();
        return response()->json(['seller' => $seller, 'commissioner' => $commissioner], 201);
    }
}
