<?php

namespace App\Http\Controllers;

use App\Buyer;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    function index() {
        $buyers = Buyer::where('user_id', $this->userId())
                        ->orderBy('created_at', 'DESC')
                        ->with('addresses')
                        ->with(['messages' => function($q) {
                            $q->orderBy('id', 'DESC')
                            ->with('article.images')
                            ->with('article.variants');
                        }])
                        ->get();
        return response()->json(['buyers' => $buyers], 200);
    }
}
