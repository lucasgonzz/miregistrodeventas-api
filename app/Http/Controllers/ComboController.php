<?php

namespace App\Http\Controllers;

use App\Combo;
use App\Http\Controllers\Helpers\ComboHelper;
use Illuminate\Http\Request;

class ComboController extends Controller
{

    public function index() {
        $models = Combo::where('user_id', $this->userId())
                        ->orderBy('created_at', 'DESC')
                        ->with('articles')
                        ->get();
        $models = ComboHelper::setArticles($models);
        return response()->json(['models' => $models], 200);
    }

    public function store(Request $request) {
        $model = Combo::create([
            'name'      => $request->name,
            'price'     => $request->price,
            'user_id'   => $this->userId(),
        ]);
        ComboHelper::attachArticles($model, $request->articles);
        $model = ComboHelper::getFullModel($model->id);
        return response()->json(['model' => $model], 201);
    }

    public function update(Request $request, $id) {
        $model = Combo::find($id);
        $model->name = $request->name;
        $model->price = $request->price;
        $model->save();
        ComboHelper::attachArticles($model, $request->articles);
        $model = ComboHelper::getFullModel($model->id);
        return response()->json(['model' => $model], 200);
    }

    public function destroy($id) {
        $model = Combo::find($id);
        $model->delete();
        return response(null, 200);
    }
}
