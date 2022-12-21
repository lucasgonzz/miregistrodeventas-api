<?php

namespace App\Http\Controllers;

use App\CreditCardPaymentPlan;
use Illuminate\Http\Request;

class CreditCardPaymentPlanController extends Controller
{

    function store(Request $request) {
        $model = CreditCardPaymentPlan::create([
            'installments'  => $request->installments,
            'surchage'      => $request->surchage,
        ]);
        return response()->json(['model' => $model], 201);
    }

    function update(Request $request, $id) {
        $model = CreditCardPaymentPlan::find($id);
        $model->installments  = $request->installments;
        $model->surchage      = $request->surchage;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    function destroy($id) {
        $model = CreditCardPaymentPlan::find($id);
        $model->delete();
        return response(null, 200);
    }

}
