<?php

namespace App\Http\Controllers;

use App\CreditCard;
use App\CreditCardPaymentPlan;
use App\Http\Controllers\Helpers\CreditCardHelper;
use Illuminate\Http\Request;

class CreditCardController extends Controller
{
    function index() {
        $models = CreditCard::where('user_id', $this->userId())
                            ->withAll()
                            ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = CreditCard::create([
            'name'      => $request->name,
            'user_id'   => $this->userId()
        ]);
        CreditCardHelper::attachCreditCardPaymentPlans($model, $request->credit_card_payment_plans);
        return response()->json(['model' => $this->fullModel('App\CreditCard', $model->id)], 201);
    }

    function update(Request $request, $id) {
        $model = CreditCard::find($id);
        $model->name = $request->name;
        $model->save();
        CreditCardHelper::attachCreditCardPaymentPlans($model, $request->credit_card_payment_plans);
        return response()->json(['model' => $this->fullModel('App\CreditCard', $model->id)], 201);
    }

    function destroy($id) {
        $model = CreditCard::find($id);
        $payment_plans = CreditCardPaymentPlan::where('credit_card_id', $model->id)
                                            ->get();
        foreach ($payment_plans as $payment_plan) {
            $payment_plan->delete();
        }
        $model->delete();
        return response(null, 200);
    }
}
