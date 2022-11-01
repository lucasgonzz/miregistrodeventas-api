<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ProviderHelper;
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
        $model = Provider::create([
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
        ProviderHelper::attachProviderPriceLists($model, $request->provider_price_lists);
        return response()->json(['model' => $this->getFullModel($model->id)], 201);
    }

    function update(Request $request, $id) {
        $model = Provider::find($id);
        $model->name = ucwords($request->name);
        $model->phone = ucwords($request->phone);
        $model->address = ucwords($request->address);
        $model->email = $request->email;
        $model->razon_social = $request->razon_social;
        $model->cuit = $request->cuit;
        $model->observations = $request->observations;
        $model->percentage_gain = $request->percentage_gain;
        $model->location_id = $request->location_id;
        $model->iva_condition_id = $request->iva_condition_id;
        $model->save();
        ProviderHelper::attachProviderPriceLists($model, $request->provider_price_lists);
        return response()->json(['model' => $this->getFullModel($model->id)], 200);
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
