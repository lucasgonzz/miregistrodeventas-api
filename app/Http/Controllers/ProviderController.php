<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProvidersImport;
use App\Provider;
use Illuminate\Http\Request;

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
                            ->orderBy('name', 'ASC')
                            ->get();
        return response()->json(['providers' => $providers], 200);
    }

    function store(Request $request) {
        $provider = Provider::create([
            'num'               => $this->num('providers'),
            'name'              => ucwords($request->name),
            'phone'             => $request->phone,
            'address'           => ucwords($request->address),
            'email'             => $request->email,
            'razon_social'      => $request->razon_social,
            'cuit'              => $request->cuit,
            'observations'      => $request->observations,
            'location_id'       => $request->location_id,
            'iva_condition_id'  => $request->iva_condition_id,
            'user_id'           => $this->getArticleOwnerId(),
        ]);
        return response()->json(['provider' => $provider], 201);
    }

    function update(Request $request, $id) {
        $provider = Provider::find($id);
        $provider->name = ucwords($request->name);
        $provider->phone = ucwords($request->phone);
        $provider->address = ucwords($request->address);
        $provider->email = $request->email;
        $provider->razon_social = $request->razon_social;
        $provider->cuit = $request->cuit;
        $provider->observations = $request->observations;
        $provider->location_id = $request->location_id;
        $provider->iva_condition_id = $request->iva_condition_id;
        $provider->save();
        return response()->json(['provider' => $provider], 200);
    }

    function destroy($id) {
    	$provider = Provider::find($id);
        $provider->update(['status' => 'inactive']);
        return response(null, 200);
    }

    function import(Request $request) {
        Excel::import(new ProvidersImport, $request->file('providers'));
    }

}
