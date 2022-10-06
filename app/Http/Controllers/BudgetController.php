<?php

namespace App\Http\Controllers;

use App\Budget;
use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\ClientHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\OrderProductionHelper;
use App\Http\Controllers\Helpers\Pdf\BudgetPdf;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

    function index($from_date, $until_date = null) {
        $models = Budget::where('user_id', $this->userId());
        if (!is_null($until_date)) {
            $models = $models->whereDate('created_at', '>=', $from_date)
                            ->whereDate('created_at', '<=', $until_date);
        } else {
            $models = $models->whereDate('created_at', $from_date);
        }
        $models = $models->withAll()
                        ->orderBy('created_at', 'DESC')
                        ->get();
        $models = BudgetHelper::getFormatedNum($models);
        return response()->json(['models' => $models], 200);
    }

    function previusDays($index) {
        $days = GeneralHelper::previusDays('App\Budget', $index);
        return response()->json(['days' => $days], 200);
    }

    function store(Request $request) {
        $budget = Budget::create([
            'num'                       => BudgetHelper::getNum(),
            'client_id'                 => $request->client_id,
            'start_at'                  => $request->start_at,
            'finish_at'                 => $request->finish_at,
            'observations'              => $request->observations,
            'budget_status_id'          => $request->budget_status_id,
            'user_id'                   => $this->userId(),
        ]);
        $old_total = BudgetHelper::getTotal($budget);
        BudgetHelper::attachArticles($budget, $request->articles);
        BudgetHelper::checkStatus($budget, $old_total);
        // BudgetHelper::attachOptionalStatuses($budget, $request->optional_statuses);
        BudgetHelper::sendMail($budget, $request->send_mail);

        return response()->json(['model' => BudgetHelper::getFullModel($budget->id)], 200);
    }

    function update(Request $request) {
        $budget = Budget::find($request->id);
        $budget->client_id = $request->client_id;
        $budget->start_at = $request->start_at;
        $budget->finish_at = $request->finish_at;
        $budget->observations = $request->observations;
        $budget->budget_status_id = $request->budget_status_id;
        $budget->save();
        BudgetHelper::attachArticles($budget, $request->articles);
        BudgetHelper::checkStatus($budget);
        return response()->json(['model' => BudgetHelper::getFullModel($budget->id)], 200);
    }

    function delete($id) {
        $budget = Budget::find($id);
        if (BudgetHelper::deleteCurrentAcount($budget)) {
            CurrentAcountHelper::checkSaldos('client', $budget->client_id);
        }
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

    function pdf($id) {
        $budget = BudgetHelper::getFullModel($id);
        $pdf = new BudgetPdf($budget);
    }
}
