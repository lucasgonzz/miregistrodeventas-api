<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

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
