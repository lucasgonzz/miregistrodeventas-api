<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Carbon\Carbon;

class CategoryController extends Controller
{

    function getArticleOwnerId() {
        $user = Auth()->user();
        if (is_null($user->owner_id)) {
            return $user->id;
        } else {
            return $user->owner_id;
        }
    }

    function mostView($weeks_ago) {
        $categories = Category::where('user_id', $this->userId())
                                ->with(['views' => function($q) use($weeks_ago) {
                                    $q->where('created_at', '>', Carbon::now()->subWeeks($weeks_ago));
                                }])
                                ->withCount('articles')
                                ->get();
        return response()->json(['categories' => $categories], 200);
    }

    function index() {
    	return Category::where('user_id', $this->getArticleOwnerId())
    					->get();
    }

    function store(Request $request) {
    	return Category::create([
    		'name'    => ucwords($request->name),
    		'user_id' => $this->getArticleOwnerId(),
    	]);
    }

    function delete($id) {
        $category = Category::find($id);
        $category->delete();
    }
}
