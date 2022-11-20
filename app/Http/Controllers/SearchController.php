<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    function search(Request $request, $model_name) {
        $model_name = GeneralHelper::getModelName($model_name);
        $models = $model_name::where('user_id', $this->userId());
        foreach ($request->filters as $filter) {
            if ($filter['type'] == 'number') {
                if ($filter['number_type'] == 'min' && $filter['value'] != '') {
                    $models = $models->where($filter['key'], '<', $filter['value']);
                }
                if ($filter['number_type'] == 'equal' && $filter['value'] != '') {
                    $models = $models->where($filter['key'], '=', $filter['value']);
                }
                if ($filter['number_type'] == 'max' && $filter['value'] != '') {
                    $models = $models->where($filter['key'], '>', $filter['value']);
                }
            } elseif ($filter['value'] != 0) {
                $models = $models->where($filter['key'], $filter['value']);
            }
        }
        $models = $models->withAll()
                        ->get();
        return response()->json(['models' => $models], 200);
    }
}
