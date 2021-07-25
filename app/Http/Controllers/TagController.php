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
        $tag = Tag::create([
            'name' => StringHelper::modelName($request->name),
        ]);
        return response()->json(['tag' => $tag], 201);
    }
}
