<?php

namespace App\Http\Controllers;

use App\Imports\ProvidersImport;
use App\Provider;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
    	$models = Provider::where('user_id', $this->getArticleOwnerId())
                            ->where('status', 'active')
                            ->orderBy('name', 'ASC')
                            ->withAll()
                            ->get();
        return response()->json(['models' => $models], 200);
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
            'percentage_gain'   => $request->percentage_gain,
            'location_id'       => $request->location_id,
            'iva_condition_id'  => $request->iva_condition_id,
            'user_id'           => $this->getArticleOwnerId(),
        ]);
        return response()->json(['model' => $this->getFullModel($provider->id)], 201);
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
        $provider->percentage_gain = $request->percentage_gain;
        $provider->location_id = $request->location_id;
        $provider->iva_condition_id = $request->iva_condition_id;
        $provider->save();
        return response()->json(['model' => $this->getFullModel($provider->id)], 200);
    }

    function setComercioCityUser(Request $request) {
        $user = User::where('company_name', $request->company_name)
                        ->first();
        if (!is_null($user)) {
            $provider = Provider::find($request->model_id);
            $provider->comercio_city_user_id = $user->id;
            $provider->save();
            return response()->json(['user_finded' => true, 'model' => $this->fullModel('App\Provider', $provider->id), 200]);
        }
        return response()->json(['user_finded' => false, 200]);
    }

    function destroy($id) {
    	$provider = Provider::find($id);
        $provider->update(['status' => 'inactive']);
        return response(null, 200);
    }

    function import(Request $request) {
        Excel::import(new ProvidersImport, $request->file('providers'));
    }

    function getFullModel($id) {
        $model = Provider::where('id', $id)
                        ->withAll()
                        ->first();
        return $model;
    }

}
