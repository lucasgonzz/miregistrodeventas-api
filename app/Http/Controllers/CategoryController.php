<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Controllers\Helpers\StringHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    function index() {
    	$models = Category::where('user_id', $this->userId())
                                ->where('status', 'active')
                                ->orderBy('name', 'ASC')
    					       ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = Category::create([
            'name'              => $request->name,
            'user_id'           => $this->userId(),
        ]);
        $model = Category::where('id', $model->id)
                                ->first();
        return response()->json(['model' => $model], 201);
    }

    function update(Request $request) {
        $model = Category::find($request->id);
        $model->name = $request->name;
        $model->save();
        $model = Category::where('id', $model->id)
                                ->first();
        return response()->json(['model' => $model], 200);
    }

    function destroy($id) {
        $model = Category::find($id);
        $model->status = 'inactive';
        $model->save();
    }
}
