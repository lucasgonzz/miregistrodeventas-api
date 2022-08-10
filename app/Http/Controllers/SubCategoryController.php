<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ArticleHelper;
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

    function forVender($ids) {
        $sub_categories = [];
        foreach (explode('-', $ids) as $id) {
            $sub_category = SubCategory::where('id', $id)
                                    ->with(['articles' => function($q) {
                                        $q->withAll();
                                    }])
                                    ->first();
            $sub_category->articles = ArticleHelper::setPrices($sub_category->articles);
            $sub_categories[] = $sub_category;
        }
        return response()->json(['sub_categories' => $sub_categories], 200);
    }

    function mostViewed($weeks_ago) {
        $sub_categories = SubCategory::where('user_id', $this->userId())
                                ->where('status', 'active')
                                // ->with(['views' => function($q) use($weeks_ago) {
                                //     $q->where('created_at', '>', Carbon::now()->subWeeks($weeks_ago));
                                // }])
                                // ->withCount('articles')
                                // ->with('views')
                                // ->with('views.buyer')
                                // ->withCount('views')
                                ->get();  
        $sub_categories = SubCategory::where('user_id', $this->userId())
                                    ->where('status', 'active')
                                    ->with('category')
                                    ->get();
        return response()->json(['sub_categories' => $sub_categories], 200);
    }

    function store(Request $request) {
    	$sub_category = SubCategory::create([
    		'name' 		        => $request->name,
    		'category_id'       => $request->category_id,
            'show_in_vender'    => $request->show_in_vender,
    		'user_id' 	        => $this->userId(),
    	]);
        $sub_category = SubCategory::where('id', $sub_category->id)
                                    ->with('category')
                                    ->first();
    	return response()->json(['sub_category' => $sub_category], 201);
    }

    function update(Request $request) {
        $sub_category = SubCategory::find($request->id);
        $sub_category->name = $request->name;
        $sub_category->category_id = $request->category_id;
        $sub_category->show_in_vender = $request->show_in_vender;
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
