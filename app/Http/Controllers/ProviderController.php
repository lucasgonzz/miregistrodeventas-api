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
    	return Provider::where('user_id', $this->getArticleOwnerId())->get();
    }

    function store($provider_name) {
        // return $this->getArticleOwnerId();
        $provider = Provider::create([
            'name' => ucwords($provider_name),
            'user_id' => $this->getArticleOwnerId(),
        ]);
        // return $request->provider['name'];
        // $provider = new Provider;
        // $provider->name = ucwords($request->provider['name']);
        // $provider->user_id = $this->getArticleOwnerId();
        // $provider->save();
        return $provider;
    }

    function delete($id) {
    	Provider::find($id)->delete();
    }
}
