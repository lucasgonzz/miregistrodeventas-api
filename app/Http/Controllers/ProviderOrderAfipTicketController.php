<?php

namespace App\Http\Controllers;

use App\ProviderOrderAfipTicket;
use Illuminate\Http\Request;

class ProviderOrderAfipTicketController extends Controller
{

    function store(Request $request) {
        $model = ProviderOrderAfipTicket::create([
            'code'                  => $request->code,
            'issued_at'             => $request->issued_at,
            'total'                 => $request->total,
            'provider_order_id'     => $request->model_id,
        ]);
        return response()->json(['model' => $model], 201);
    }

    function update(Request $request, $id) {
        $model = ProviderOrderAfipTicket::find($id);
        $model->code                  = $request->code;
        $model->issued_at             = $request->issued_at;
        $model->total                 = $request->total;
        $model->provider_order_id     = $request->model_id;
        $model->save();
        return response()->json(['model' => $model], 201);
    }

    function destroy($id) {
        $model = ProviderOrderAfipTicket::find($id);
        $model->delete();
        return response(null, 200);
    }
}
