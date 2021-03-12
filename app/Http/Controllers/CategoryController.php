<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Carbon\Carbon;

class CategoryController extends Controller
{

    function mostView($weeks_ago) {
        $categories = Category::where('user_id', $this->userId())
                                ->where('status', 'active')
                                ->with(['views' => function($q) use($weeks_ago) {
                                    $q->where('created_at', '>', Carbon::now()->subWeeks($weeks_ago));
                                }])
                                ->withCount('articles')
                                ->get();
        return response()->json(['categories' => $categories], 200);
    }

    function index() {
    	$categories = Category::where('user_id', $this->userId())
                                ->where('status', 'active')
    					       ->get();
        return response()->json(['categories' => $categories], 200);
    }

    function store(Request $request) {
    	$category = Category::create([
    		'name'    => ucwords($request->name),
    		'user_id' => $this->userId(),
    	]);
        return response()->json(['category' => $category], 201);
    }

    function delete($id) {
        $category = Category::find($id);
        $category->status = 'inactive';
        $category->save();
    }
}
