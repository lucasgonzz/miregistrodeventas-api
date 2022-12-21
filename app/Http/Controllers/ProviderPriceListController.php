<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\GeneralHelper;
use App\ProviderPriceList;
use Illuminate\Http\Request;

class ProviderPriceListController extends Controller
{

    function store(Request $request) {
        $model = ProviderPriceList::create([
            'name'            => $request->name,
            'percentage'      => $request->percentage,
            'provider_id'     => $request->model_id,
        ]);
        return response()->json(['model' => $model], 201);
    }

    function update(Request $request, $id) {
        $model = ProviderPriceList::find($id);
        $last_percentage = $model->percentage;
        $model->name                  = $request->name;
        $model->percentage            = $request->percentage;
        $model->save();
        GeneralHelper::checkNewValuesForArticlesPrices($last_percentage, $model->percentage, 'provider_id', $model->provider_id);
        return response()->json(['model' => $model], 201);
    }

    function destroy($id) {
        $model = ProviderPriceList::find($id);
        $model->delete();
        return response(null, 200);
    }
}
