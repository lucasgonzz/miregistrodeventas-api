<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\StringHelper;
use App\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    function index() {
    	$models = SubCategory::where('user_id', $this->userId())
    								->where('status', 'active')
                                    ->with('category')
                                    ->orderBy('created_at', 'DESC')
    								->get();
    	return response()->json(['models' => $models], 200);
    }

    function forVender($ids) {
        $models = [];
        foreach (explode('-', $ids) as $id) {
            $model = SubCategory::where('id', $id)
                                    ->with(['articles' => function($q) {
                                        $q->withAll();
                                    }])
                                    ->first();
            $model->articles = ArticleHelper::setPrices($model->articles);
            $models[] = $model;
        }
        return response()->json(['models' => $models], 200);
    }

    function mostViewed($weeks_ago) {
        $models = SubCategory::where('user_id', $this->userId())
                                ->where('status', 'active')
                                ->get();  
        $models = SubCategory::where('user_id', $this->userId())
                                    ->where('status', 'active')
                                    ->with('category')
                                    ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
    	$model = SubCategory::create([
    		'name' 		        => $request->name,
    		'category_id'       => $request->category_id,
            'show_in_vender'    => $request->show_in_vender,
    		'user_id' 	        => $this->userId(),
    	]);
        $model = SubCategory::where('id', $model->id)
                                    ->with('category')
                                    ->first();
    	return response()->json(['model' => $model], 201);
    }

    function update(Request $request) {
        $model = SubCategory::find($request->id);
        $model->name = $request->name;
        $model->category_id = $request->category_id;
        $model->show_in_vender = $request->show_in_vender;
        $model->save();
        $model = SubCategory::where('id', $model->id)
                                    ->with('category')
                                    ->first();
        return response()->json(['model' => $model], 200);
    }

    function destroy($id) {
        $model = SubCategory::find($id);
    	$model->status = 'inactive';
    	$model->save();
    	return response(null, 200);
    }
}
