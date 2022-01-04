<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\PricesListsPdf;
use App\Http\Controllers\Helpers\StringHelper;
use App\PricesList;
use Illuminate\Http\Request;

class PricesListController extends Controller
{

    function index() {
        $prices_lists = PricesList::where('user_id', $this->userId())
                                    ->with('articles.images')
                                    ->get();
        return response()->json(['prices_lists' => $prices_lists], 200);
    }

    function store(Request $request) {
        $prices_list = new PricesList();
        $prices_list->name = StringHelper::modelName($request->name);
        $prices_list->user_id = $this->userId();
        $prices_list->save();
        foreach ($request->articles as $article) {
            $prices_list->articles()->attach($article['id']);
        }
        $prices_list = PricesList::where('id', $prices_list->id)
                                    ->with('articles.images')
                                    ->first();
        return response()->json(['prices_list' => $prices_list], 200);
    }

    function pdf($id) {
        $prices_list = PricesList::where('id', $id)
                                    ->with('articles.images')
                                    ->first();
        $pdf = new PricesListsPdf($prices_list);
    }
}
