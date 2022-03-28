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
    	$providers = Provider::where('user_id', $this->getArticleOwnerId())
                            ->where('status', 'active')
                            ->get();
        return response()->json(['providers' => $providers], 200);
    }

    function store(Request $request) {
        $provider = Provider::create([
            'name' => ucwords($request->name),
            'address' => ucwords($request->address),
            'user_id' => $this->getArticleOwnerId(),
        ]);
        return response()->json(['provider' => $provider], 201);
    }

    function update(Request $request) {
        $provider = Provider::find($request->id);
        $provider->name = ucwords($request->name);
        $provider->address = ucwords($request->address);
        $provider->save();
        return response()->json(['provider' => $provider], 200);
    }

    function delete($id) {
    	$provider = Provider::find($id);
        $provider->update(['status' => 'inactive']);
        return response(null, 200);
    }
}
