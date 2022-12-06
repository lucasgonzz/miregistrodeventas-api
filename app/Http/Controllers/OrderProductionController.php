<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ClientHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\OrderProductionCurrentAcountHelper;
use App\Http\Controllers\Helpers\OrderProductionHelper;
use App\Http\Controllers\Helpers\OrderProductionProviderOrderHelper;
use App\Http\Controllers\Helpers\Pdf\OrderProductionArticlesPdf;
use App\Http\Controllers\Helpers\Pdf\OrderProductionPdf;
use App\OrderProduction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderProductionController extends Controller
{

    function index($from_date, $until_date = null) {
        $models = OrderProduction::where('user_id', $this->userId());
        if (!is_null($until_date)) {
            $models = $models->whereDate('created_at', '>=', $from_date)
                            ->whereDate('created_at', '<=', $until_date);
        } else {
            $models = $models->whereDate('created_at', $from_date);
        }
        $models = $models->withAll()
                        ->orderBy('created_at', 'DESC')
                        ->get();
        $models = OrderProductionHelper::setArticles($models);
        return response()->json(['models' => $models], 200);
    }

    function previusDays($index) {
        $days = GeneralHelper::previusDays('App\OrderProduction', $index);
        return response()->json(['days' => $days], 200);
    }

    function store(Request $request) {
        $model = OrderProduction::create([
            'num'                           => $this->num('order_productions'),
            'client_id'                     => $request->client_id,
            'observations'                  => $request->observations,
            'start_at'                      => Carbon::parse($request->start_at),
            'finish_at'                     => Carbon::parse($request->finish_at),
            'order_production_status_id'    => OrderProductionHelper::getFisrtStatus(),
            'user_id'                       => $this->userId(), 
        ]);
        OrderProductionHelper::attachArticles($model, $request->articles);
        // OrderProductionHelper::sendCreatedMail($model, $request->send_mail);
        $model = OrderProductionHelper::setArticles([$this->getFullModel($model->id)])[0];
        return response()->json(['model' => $model], 201);
    }

    function update(Request $request) {
        $model = OrderProduction::find($request->id);
        $model->client_id                    = $request->client_id;
        $model->order_production_status_id   = $request->order_production_status_id;
        $model->save();
        OrderProductionHelper::attachArticles($model, $request->articles);
        OrderProductionHelper::sendUpdatedMail($model);
        // return response()->json(['model' => $this->getFullModel($model->id)], 200);
        $model = OrderProductionHelper::setArticles([$this->getFullModel($model->id)])[0];
        return response()->json(['model' => $model], 200);
    }

    function finish(Request $request, $id) {
        $model = OrderProduction::find($id);
        $model->finished = 1;
        $model->save();
        if ($request->save_current_acount) {
            OrderProductionCurrentAcountHelper::saveCurrentAcount($model);
        }
        if ($request->save_provider_order) {
            OrderProductionProviderOrderHelper::createProviderOrder($model);
        }
        return response()->json(['model' => $this->fullModel('App\OrderProduction', $model->id), 200]);
    }

    function setPdf(Request $request) {
        $link = Storage::disk('public')->put('pdf', $request->file('pdf'), 'public');
        $link = explode('/', $link)[1];
        $order_production = OrderProduction::find($request->order_production_id);
        $order_production->pdf = $link;
        $order_production->save();
        return response()->json(['model' => $this->getFullModel($order_production->id), 'link' => $link], 200);
    }

    function delete($id) {
        $order = OrderProduction::find($id);
        $order->delete();
        return response(null, 200);
    }

    function getFullModel($id) {
        $order_production = OrderProduction::where('id', $id)
                                            ->withAll()
                                            ->first();
        return $order_production;                              
    }

    function pdf($id) {
        $order_production = OrderProduction::find($id);
        $pdf = new OrderProductionPdf($order_production);
    }

    function articlesPdf($id) {
        $order_production = OrderProduction::find($id);
        $pdf = new OrderProductionArticlesPdf($order_production);
    }

}
