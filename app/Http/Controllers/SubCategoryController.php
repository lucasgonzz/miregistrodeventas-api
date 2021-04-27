<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\StringHelper;
use App\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    function index() {
    	$sub_categories = SubCategory::where('user_id', $this->userId())
    								->where('status', 'active')
                                    ->with('category')
    								->get();
    	return response()->json(['sub_categories' => $sub_categories], 200);
    }

    function store(Request $request) {
    	$sub_category = SubCategory::create([
    		'name' 		  => StringHelper::modelName($request->name),
    		'category_id' => $request->category_id,
    		'user_id' 	  => $this->userId(),
    	]);
        $sub_category = SubCategory::where('id', $sub_category->id)
                                    ->with('category')
                                    ->first();
    	return response()->json(['sub_category' => $sub_category], 201);
    }

    function update(Request $request) {
        $sub_category = SubCategory::find($request->id);
        $sub_category->name = StringHelper::modelName($request->name);
        $sub_category->category_id = $request->category_id;
        $sub_category->save();
        $sub_category = SubCategory::where('id', $sub_category->id)
                                    ->with('category')
                                    ->first();
        return response()->json(['sub_category' => $sub_category], 200);
    }

    function delete($id) {
        $sub_category = SubCategory::find($id);
    	$sub_category->status = 'inactive';
    	$sub_category->save();
    	return response(null, 200);
    }
}
