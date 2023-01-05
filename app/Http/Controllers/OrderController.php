<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\Events\OrderCanceled as OrderCanceledEvent;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\CartHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\MessageHelper;
use App\Http\Controllers\Helpers\OrderHelper;
use App\Http\Controllers\Helpers\Pdf\OrderPdf;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Notifications\OrderCanceled as OrderCanceledNotification;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderFinished;
use App\Order;
use App\Sale;
use Illuminate\Http\Request;

class OrderController extends Controller {

    function index($from_date, $until_date = null) {
        $models = Order::where('user_id', $this->userId());
        if (!is_null($until_date)) {
            $models = $models->whereDate('created_at', '>=', $from_date)
                            ->whereDate('created_at', '<=', $until_date);
        } else {
            $models = $models->whereDate('created_at', $from_date);
        }
        $models = $models->withAll()
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return response()->json(['models' => $models], 200);
    }

    function indexUnconfirmed() {
        $models = Order::where('user_id', $this->userId())
                        ->where('order_status_id', 1)
                        ->withAll()
                        ->get();
        return response()->json(['models' => $models], 200);
    }

    function show($id) {
        return response()->json(['model' => $this->fullModel('App\Order', $id)], 200);
    }

    function previusDays($index) {
        $days = GeneralHelper::previusDays('App\Order', $index);
        return response()->json(['days' => $days], 200);
    }

    function update(Request $request, $id) {
        $model = Order::find($id);
        OrderHelper::attachArticles($model, $request->articles);
        return response()->json(['model' => $this->fullModel('App\Order', $id)], 200);
    }

    function updateStatus(Request $request, $id) {
        $model = Order::find($id);
        OrderHelper::discountArticleStock($model);
        $model->order_status_id = $request->order_status_id;
        $model->save();
        $model = Order::find($id);
        OrderHelper::sendMail($model);
        OrderHelper::saveSale($model);
        return response()->json(['model' => $this->fullModel('App\Order', $model->id)], 200);
    }

    function cancel(Request $request, $id) {
        $model = Order::find($id);
        $model->order_status_id = $this->getModelBy('order_statuses', 'name', 'Cancelado', false, 'id');
        $model->save();
        OrderHelper::restartArticleStock($model);
        MessageHelper::sendOrderCanceledMessage($request->description, $model);
        return response()->json(['model' => $this->fullModel('App\Order', $model->id)], 200);
    }

    function pdf($id) {
        $model = Order::find($id);
        new OrderPdf($model);
    }
}
