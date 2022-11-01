<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    function search(Request $request, $model_name) {
        $model_name = 'App\-'.ucfirst($model_name);
        $model_name = str_replace('-', '', $model_name);
        $models = $model_name::where('user_id', $this->userId());
        foreach ($request->filter as $key => $value) {
            if ($value != 0) {
                $models = $models->where($key, $value);
            }
        }
        $models = $models->withAll()
                        ->get();
        return response()->json(['models' => $models], 200);
    }
}
