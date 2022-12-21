<?php

namespace App\Http\Controllers;

use App\Exports\ProviderExport;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Imports\ProvidersImport;
use App\Provider;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProviderController extends Controller
{

    function index() {
    	$models = Provider::where('user_id', $this->userId())
                            ->where('status', 'active')
                            ->orderBy('created_at', 'DESC')
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
            'dolar'             => $request->dolar,
            'location_id'       => $request->location_id,
            'iva_condition_id'  => $request->iva_condition_id,
            'user_id'           => $this->userId(),
        ]);
        return response()->json(['model' => $this->fullModel('App\Provider', $model->id)], 201);
    }

    function update(Request $request, $id) {
        $model = Provider::find($id);
        $last_percentage_gain       = $model->percentage_gain;
        $last_dolar                 = $model->dolar;
        $model->name                = ucwords($request->name);
        $model->phone               = ucwords($request->phone);
        $model->address             = ucwords($request->address);
        $model->email               = $request->email;
        $model->razon_social        = $request->razon_social;
        $model->cuit                = $request->cuit;
        $model->observations        = $request->observations;
        $model->percentage_gain     = $request->percentage_gain;
        $model->dolar               = $request->dolar;
        $model->location_id         = $request->location_id;
        $model->iva_condition_id    = $request->iva_condition_id;
        $model->save();
        GeneralHelper::checkNewValuesForArticlesPrices($last_percentage_gain, $model->percentage_gain, 'provider_id', $model->id);
        GeneralHelper::checkNewValuesForArticlesPrices($last_dolar, $model->dolar, 'provider_id', $model->id);
        return response()->json(['model' => $this->fullModel('App\Provider', $model->id)], 200);
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
        $columns = GeneralHelper::getImportColumns($request);
        Excel::import(new ProvidersImport($columns, $request->start_row, $request->finish_row), $request->file('models'));
    }

    function export() {
        return Excel::download(new ProviderExport, 'comerciocity-proveedores.xlsx');
    }

}
