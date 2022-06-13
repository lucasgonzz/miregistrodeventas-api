<?php

namespace App\Http\Controllers;

use App\BudgetProductDelivery;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BudgetProductDeliveryController extends Controller
{
    function store(Request $request) {
        $budget_product_delivery = BudgetProductDelivery::create([
            'amount'            => $request->amount,
            'note'              => $request->note,
            'budget_product_id' => $request->product_id,
            'created_at'        => $request->current_date ? Carbon::now() : $request->created_at,
        ]);
        return response()->json(['budget_product_delivery' => $budget_product_delivery], 201);
    }

    function delete($id) {
        $budget_product_delivery = BudgetProductDelivery::find($id);
        $budget_product_delivery->delete();
        return response(null, 200);
    }
}
