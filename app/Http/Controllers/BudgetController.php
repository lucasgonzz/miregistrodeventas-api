<?php

namespace App\Http\Controllers;

use App\Budget;
use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\ClientHelper;
use App\Http\Controllers\Helpers\OrderProductionHelper;
use App\Http\Controllers\Helpers\Pdf\BudgetPdf;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

    function index() {
        $budgets = Budget::where('user_id', $this->userId())
                            ->with('client.iva_condition')
                            ->with('products')
                            ->with('observations')
                            ->with('order_production.status')
                            ->orderBy('id', 'DESC')
                            ->get();
        $budgets = BudgetHelper::getFormatedNum($budgets);
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
        BudgetHelper::sendMail($budget, $request->send_mail);

        return response()->json(['budget' => BudgetHelper::getFullModel($budget->id)], 200);
    }

    function update(Request $request) {
        $budget = Budget::find($request->budget['id']);
        $budget->start_at = $request->start_at;
        $budget->finish_at = $request->finish_at;
        $budget->delivery_and_placement = $request->delivery_and_placement;
        $budget->save();
        BudgetHelper::attachProducts($budget, $request->products);
        BudgetHelper::attachObservations($budget, $request->observations);
        return response()->json(['budget' => BudgetHelper::getFullModel($budget->id)], 200);
    }

    function delete($id) {
        $budget = Budget::find($id);
        BudgetHelper::deleteCurrentAcount($budget);
        BudgetHelper::deleteOrderProduction($budget);
        $budget->delete();
        return response(null, 200);
    }

    function confirm(Request $request) {
        $budget = Budget::find($request->id);
        $budget->status = 'confirmed';
        $budget->save();
        BudgetHelper::saveCurrentAcount($budget);
        $client = ClientHelper::getFullModel($budget->client_id);
        return response()->json(['budget' => BudgetHelper::getFullModel($budget->id), 'client' => $client], 200);
    }

    function pdf($only_deliveries, $id) {
        $budget = BudgetHelper::getFullModel($id);
        $pdf = new BudgetPdf($only_deliveries, $budget);
    }
}
