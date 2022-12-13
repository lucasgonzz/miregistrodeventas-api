<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    function search(Request $request, $model_name_param) {
        $model_name = GeneralHelper::getModelName($model_name_param);
        $models = $model_name::where('user_id', $this->userId());
        foreach ($request->filters as $filter) {
            if (isset($filter['type'])) {
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
        }
        if ($model_name_param == 'article' || $model_name_param == 'client' || $model_name_param == 'provider') {
            $models = $models->where('status', 'active');
        }
        $models = $models->withAll()
                        ->get();
        // if ($model_name_param == 'article') {
        //     $models = ArticleHelper::setPrices($models);
        // }
        return response()->json(['models' => $models], 200);
    }
}
