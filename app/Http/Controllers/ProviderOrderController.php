<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\Pdf\ProviderOrderPdf;
use App\Http\Controllers\Helpers\ProviderOrderHelper;
use App\ProviderOrder;
use Illuminate\Http\Request;

class ProviderOrderController extends Controller
{

    function index($from_date, $until_date = null) {
        $models = ProviderOrder::where('user_id', $this->userId());
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

    function previusDays($index) {
        $days = GeneralHelper::previusDays('App\ProviderOrder', $index);
        return response()->json(['days' => $days], 200);
    }

    function store(Request $request) {
        $provider_order = ProviderOrder::create([
            'num'         => ProviderOrderHelper::getNum(),
            'provider_id' => $request->provider_id,
            'user_id'     => $this->userId(),
        ]);
        ProviderOrderHelper::attachArticles($request->articles, $provider_order);
        // ProviderOrderHelper::sendEmail($request->send_email, $provider_order);
        $provider_order = $this->fullModel('App\ProviderOrder', $provider_order->id);
        return response()->json(['model' => $provider_order], 201);
    }

    function update(Request $request, $id) {
        $provider_order = ProviderOrder::find($id);
        $provider_order->provider_id = $request->provider_id;
        $provider_order->save();
        ProviderOrderHelper::attachArticles($request->articles, $provider_order);
        // ProviderOrderHelper::sendEmail($request->send_email, $provider_order);
        $provider_order = $this->fullModel('App\ProviderOrder', $provider_order->id);
        return response()->json(['model' => $provider_order], 201);
    }

    // function setReceived(Request $request) {
    //     $provider_order = ProviderOrder::find($request->provider_order_id);
    //     ProviderOrderHelper::updateArticleStock($provider_order, $request->article);
    //     $provider_order->articles()->updateExistingPivot($request->article['id'], [
    //                                     'received' => $request->article['received']
    //                                 ]);
    //     $article = ArticleHelper::getFullArticle($request->article['id']);
    //     $article->from_provider_order = true;
    //     return response()->json(['article' => $article], 200);
    // }

    function pdf($id) {
        $provider_order = ProviderOrder::find($id);
        $pdf = new ProviderOrderPdf($provider_order);
    }

    function destroy($id) {
        $model = ProviderOrder::find($id);
        ProviderOrderHelper::deleteCurrentAcount($model);
        $model->articles()->sync([]);
        $model->delete();
        return response(null, 200); 
    }
    
}
