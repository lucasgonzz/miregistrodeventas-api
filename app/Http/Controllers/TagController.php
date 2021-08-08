<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\StringHelper;
use App\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    function index() {
        $tags = Tag::all();
        return response()->json(['tags' => $tags], 200);
    }

    function store(Request $request) {
        $tag = $this->isTagRegister($request->name);
        if (!$tag) {
            $tag = Tag::create([
                'name' => StringHelper::modelName($request->name),
                'user_id' => $this->userId(),
            ]);
        }
        return response()->json(['tag' => $tag], 201);
    }

    function isTagRegister($name) {
        $tag = Tag::where('name', $name)->first();
        if ($tag) {
            return $tag;
        }
        return false;
    }
}
