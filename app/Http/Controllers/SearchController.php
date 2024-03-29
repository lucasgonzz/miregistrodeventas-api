<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    function search(Request $request, $model_name_param, $_filters = null) {
        $model_name = GeneralHelper::getModelName($model_name_param);
        $models = $model_name::where('user_id', $this->userId());
        if (is_null($_filters)) {
            $filters = $request->filters;
        } else {
            $filters = $_filters;
        }
        Log::info($filters);
        foreach ($filters as $filter) {
            if (isset($filter['type'])) {
                if ($filter['type'] == 'number') {
                    if ($filter['number_type'] == 'min' && $filter['value'] != '') {
                        $models = $models->where($filter['key'], '<', $filter['value']);
                        // Log::info('Filtrando por number '.$filter['text'].' min');
                    }
                    if ($filter['number_type'] == 'equal' && $filter['value'] != '') {
                        $models = $models->where($filter['key'], '=', $filter['value']);
                        // Log::info('Filtrando por number '.$filter['text'].' igual');
                    }
                    if ($filter['number_type'] == 'max' && $filter['value'] != '') {
                        $models = $models->where($filter['key'], '>', $filter['value']);
                        // Log::info('Filtrando por number '.$filter['text'].' max');
                    }
                } else if (($filter['type'] == 'text' || $filter['type'] == 'textarea') && $filter['value'] != '') {
                    $models = $models->where($filter['key'], 'like', '%'.$filter['value'].'%');
                    // Log::info('Filtrando por text '.$filter['text']);
                } else if ($filter['type'] == 'boolean' && $filter['value'] != -1) {
                    $models = $models->where($filter['key'], $filter['value']);
                    // Log::info('Filtrando por boolean '.$filter['text']);
                } else if ($filter['type'] != 'boolean' && $filter['value'] != 0) {
                    $models = $models->where($filter['key'], $filter['value']);
                    // Log::info('Filtrando por value '.$filter['text']);
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
        if (is_null($_filters)) {
            return response()->json(['models' => $models], 200);
        } else {
            return $models;
        }
    }
}
