<?php

namespace App\Http\Controllers;

use App\AfipInformation;
use Illuminate\Http\Request;

class AfipInformationController extends Controller
{

    function update(Request $request) {
        $afip_information = AfipInformation::where('user_id', $this->userId())
                                            ->first();
        if (is_null($afip_information)) {
            $afip_information = AfipInformation::create([
                'user_id' => $this->userId()
            ]);
        }
        $afip_information->razon_social = $request->razon_social;
        $afip_information->domicilio_comercial = $request->domicilio_comercial;
        $afip_information->cuit = $request->cuit;
        $afip_information->iva_condition_id = $request->iva_condition_id;
        $afip_information->ingresos_brutos = $request->ingresos_brutos;
        $afip_information->inicio_actividades = $request->inicio_actividades;
        $afip_information->punto_venta = $request->punto_venta;
        $afip_information->afip_ticket_production = $request->afip_ticket_production;
        $afip_information->save();
        return response()->json(['afip_information' => $afip_information], 200);
    }
}
