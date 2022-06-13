<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ClientHelper;
use App\Http\Controllers\Helpers\OrderProductionHelper;
use App\OrderProduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderProductionController extends Controller
{

    function index() {
        $order_productions = OrderProduction::where('user_id', $this->userId())
                                            ->with('budget.client.iva_condition')
                                            ->with('budget.products.deliveries')
                                            ->with('budget.products.article_stocks.article')
                                            ->with('budget.observations')
                                            ->with('status')
                                            ->orderBy('id', 'DESC')
                                            ->get();
        return response()->json(['order_productions' => $order_productions], 200);
    }

    function store(Request $request) {
        $order_production = OrderProduction::create([
            'budget_id'                     => $request->id,
            'order_production_status_id'    => OrderProductionHelper::getStatusId('Deposito'),
            'user_id'                       => $this->userId(), 
        ]);
        OrderProductionHelper::sendCreatedMail($order_production, $request->send_mail);
        return response()->json(['order_production' => $this->getFullModel($order_production->id)], 201);
    }

    function update(Request $request) {
        $order_production = OrderProduction::find($request->id);
        $order_production->order_production_status_id = $request->order_production_status_id;
        $order_production->save();
        OrderProductionHelper::sendUpdatedMail($order_production);
        return response()->json(['order_production' => $this->getFullModel($order_production->id)], 200);
    }

    function setPdf(Request $request) {
        $link = Storage::disk('public')->put('pdf', $request->file('pdf'), 'public');
        $link = explode('/', $link)[1];
        $order_production = OrderProduction::find($request->order_production_id);
        $order_production->pdf = $link;
        $order_production->save();
        return response()->json(['order_production' => $this->getFullModel($order_production->id), 'link' => $link], 200);
    }

    function delete($id) {
        $order = OrderProduction::find($id);
        $order->delete();
        return response(null, 200);
    }

    function getFullModel($id) {
        $order_production = OrderProduction::where('id', $id)
                                            ->with('budget.client.iva_condition')
                                            ->with('budget.products.deliveries')
                                            ->with('budget.products.article_stocks.article')
                                            ->with('budget.observations')
                                            ->with('status')
                                            ->first();
        return $order_production;                              
    }
}
