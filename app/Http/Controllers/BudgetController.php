<?php

namespace App\Http\Controllers;

use App\Budget;
use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\Pdf\BudgetPdf;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

    function index() {
        $budgets = Budget::where('user_id', $this->userId())
                            ->with('client.iva_condition')
                            ->with('products')
                            ->with('observations')
                            ->with('order_production')
                            ->orderBy('id', 'DESC')
                            ->get();
        return response()->json(['budgets' => $budgets], 200);
    }

    function store(Request $request) {
        $budget = Budget::create([
            'num'                       => BudgetHelper::getNum(),
            'client_id'                 => $request->client['id'],
            'start_at'                  => $request->start_at,
            'finish_at'                 => $request->finish_at,
            'delivery_and_placement'    => $request->delivery_and_placement,
            'user_id'                   => $this->userId(),
        ]);
        BudgetHelper::attachProducts($budget, $request->products);
        BudgetHelper::attachObservations($budget, $request->observations);
        return response()->json(['budget' => $this->getFullModel($budget->id)], 200);
    }

    function update(Request $request) {
        $budget = Budget::find($request->id);
        $budget->start_at = $request->start_at;
        $budget->finish_at = $request->finish_at;
        $budget->delivery_and_placement = $request->delivery_and_placement;
        $budget->save();
        BudgetHelper::attachProducts($budget, $request->products);
        BudgetHelper::attachObservations($budget, $request->observations);
        return response()->json(['budget' => $this->getFullModel($budget->id)], 200);
    }

    function delete($id) {
        $budget = Budget::find($id);
        $budget->delete();
        return response(null, 200);
    }

    function getFullModel($id) {
        $budget = Budget::where('id', $id)
                        ->with('client.iva_condition')
                        ->with('products')
                        ->with('observations')
                        ->with('order_production')
                        ->first();
        return $budget;
    }

    function pdf($id) {
        $budget = Budget::find($id);
        $pdf = new BudgetPdf($budget);
    }
}
