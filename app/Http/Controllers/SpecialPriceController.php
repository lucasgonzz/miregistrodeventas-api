<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SpecialPrice;

class SpecialPriceController extends Controller
{
    public function index() {
    	return SpecialPrice::where('user_id', $this->userId())
    						->get();
    }

    public function store(Request $request) {
    	return SpecialPrice::create([
    		'name' 	  => ucwords($request->name),
    		'user_id' => $this->userId()
    	]);
    }

    public function delete($id) {
    	$special_price = SpecialPrice::find($id);
    	$special_price->delete();
    }
}
