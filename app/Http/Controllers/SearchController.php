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
                    Log::info('Filtrando por '.$filter['text'].' min');
                }
                if ($filter['number_type'] == 'equal' && $filter['value'] != '') {
                    $models = $models->where($filter['key'], '=', $filter['value']);
                    Log::info('Filtrando por '.$filter['text'].' igual');
                }
                if ($filter['number_type'] == 'max' && $filter['value'] != '') {
                    $models = $models->where($filter['key'], '>', $filter['value']);
                    Log::info('Filtrando por '.$filter['text'].' max');
                }
            } else if (($filter['type'] == 'text' || $filter['type'] == 'textarea') && $filter['value'] != '') {
                $models = $models->where($filter['key'], 'like', '%'.$filter['value'].'%');
                Log::info('Filtrando por '.$filter['text']);
            } else if ($filter['value'] != 0) {
                $models = $models->where($filter['key'], $filter['value']);
                Log::info('Filtrando por '.$filter['text']);
            }
        }
        $models = $models->withAll()
                        ->get();
        return response()->json(['models' => $models], 200);
    }
}
