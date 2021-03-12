<?php

namespace App\Http\Controllers;

use App\Commissioner;
use App\CurrentAcount;
use App\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index() {
    	$sellers = Seller::where('user_id', $this->userId())
    						->get();
    	return response()->json(['sellers' => $sellers], 200);
    }

    // function currentAcounts($commissioner_id) {
    //     $seller_id = Commissioner::find($commissioner_id)->seller_id;
    //     $current_acounts = CurrentAcount::where('status', 'pagado')
    //                                     ->where('seller_id', $seller_id)
    //                                     ->with('sale.commissioners')
    //                                     ->with('client')
    //                                     ->get();
    //     return response()->json(['current_acounts' => $current_acounts], 200);
    // }
}
