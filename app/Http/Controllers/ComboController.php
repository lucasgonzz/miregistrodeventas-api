<?php

namespace App\Http\Controllers;

use App\Combo;
use App\Http\Controllers\Helpers\ComboHelper;
use Illuminate\Http\Request;

class ComboController extends Controller
{

    public function index() {
        $combos = Combo::where('user_id', $this->userId())
                        ->orderBy('created_at', 'DESC')
                        ->with('articles')
                        ->get();
        return response()->json(['combos' => $combos], 200);
    }

    public function store(Request $request) {
        $combo = Combo::create([
            'name'      => $request->name,
            'price'     => $request->price,
            'user_id'   => $this->userId(),
        ]);
        ComboHelper::attachArticles($combo, $request->articles);
        $combo = ComboHelper::getFullModel($combo->id);
        return response()->json(['combo' => $combo], 201);
    }

    public function update(Request $request, $id) {
        $combo = Combo::find($id);
        $combo->name = $request->name;
        $combo->price = $request->price;
        $combo->save();
        foreach ($request->articles as $article) {
            if (!ComboHelper::hasArticle($combo, $article)) {
                $combo->articles()->attach($article['id']);
            }
        }
        $combo = ComboHelper::getFullModel($combo->id);
        return response()->json(['combo' => $combo], 200);
    }

    public function destroy($id) {
        $combo = Combo::find($id);
        $combo->delete();
        return response(null, 200);
    }
}
