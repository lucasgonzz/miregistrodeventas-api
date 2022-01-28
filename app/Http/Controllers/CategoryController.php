<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Controllers\Helpers\StringHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    function index() {
    	$categories = Category::where('user_id', $this->userId())
                                ->where('status', 'active')
                                ->orderBy('name', 'ASC')
                                ->with('icon')
    					       ->get();
        return response()->json(['categories' => $categories], 200);
    }

    function store(Request $request) {
        $category = Category::create([
            'name'    => ucfirst($request->name),
            'icon_id' => $request->icon_id,
            'user_id' => $this->userId(),
        ]);
        $category = Category::where('id', $category->id)
                                ->with('icon')
                                ->first();
        return response()->json(['category' => $category], 201);
    }

    function update(Request $request) {
        $category = Category::find($request->id);
        $category->name = StringHelper::modelName($request->name);
        $category->save();
        return response()->json(['category' => $category], 200);
    }

    function delete($id) {
        $category = Category::find($id);
        $category->status = 'inactive';
        $category->save();
    }
}
