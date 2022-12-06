<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\PricesListsPdf;
use App\Http\Controllers\Helpers\StringHelper;
use App\PricesList;
use Illuminate\Http\Request;

class PricesListController extends Controller
{

    function index() {
        $models = PricesList::where('user_id', $this->userId())
                                    ->withAll()
                                    ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = new PricesList();
        $model->name = StringHelper::modelName($request->name);
        $model->user_id = $this->userId();
        $model->save();
        foreach ($request->articles as $article) {
            $model->articles()->attach($article['id']);
        }
        $model = PricesList::where('id', $model->id)
                                    ->with('articles.images')
                                    ->first();
        return response()->json(['model' => $model], 200);
    }

    function update(Request $request, $id) {
        $model = PricesList::find($id);
        $model->articles()->detach();
        foreach ($request->articles as $article) {
            $model->articles()->attach($article['id']);
        }
        return response()->json(['model' => $this->fullModel('App\PricesList', $id)], 200);
    }

    function hasArticle($model, $_article) {
        $has_article = false;
        foreach ($model->articles as $article) {
            if ($article->id == $_article['id']) {
                $has_article = true;
                break;
            }
        }
        return $has_article;
    }

    function destroy($id) {
        $model = PricesList::find($id);
        $model->delete();
        return response(null, 200);
    }

    function pdf($id) {
        $model = PricesList::find($id);
        $pdf = new PricesListsPdf($model);
    }
}
