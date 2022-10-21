<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\RecipeHelper;
use App\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    function index() {
        $models = Recipe::where('user_id', $this->userId())
                            ->withAll()
                            ->orderBy('created_at', 'DESC')
                            ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = Recipe::create([
            'article_id'    => $request->article_id,
            'user_id'       => $this->userId(), 
        ]);
        RecipeHelper::attachArticles($model, $request->articles);
        return response()->json(['model' => $this->fullModel('App\Recipe', $model->id)], 200);
    }

    function update(Request $request, $id) {
        $model = Recipe::find($id);
        RecipeHelper::attachArticles($model, $request->articles);
        return response()->json(['model' => $this->fullModel('App\Recipe', $model->id)], 200);
    }

    function destroy($id) {
        $model = Recipe::find($id);
        $model->delete();
        return response(null, 200);
    }
}
