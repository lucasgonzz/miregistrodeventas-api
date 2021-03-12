<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Provider;

class ProviderController extends Controller
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
    	$providers = Provider::where('user_id', $this->getArticleOwnerId())->get();
        return response()->json(['providers' => $providers], 200);
    }

    function store(Request $request) {
        $provider = Provider::create([
            'name' => ucwords($request->name),
            'user_id' => $this->getArticleOwnerId(),
        ]);
        return response()->json(['provider' => $provider], 201);
    }

    function delete($id) {
    	Provider::find($id)->delete();
    }
}
