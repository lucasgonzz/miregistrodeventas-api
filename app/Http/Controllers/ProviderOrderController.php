<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\Pdf\ProviderOrderPdf;
use App\Http\Controllers\Helpers\ProviderOrderHelper;
use App\ProviderOrder;
use Illuminate\Http\Request;

class ProviderOrderController extends Controller
{

    function index() {
        $provider_orders = ProviderOrder::where('user_id', $this->userId())
                                        ->with('provider')
                                        ->with('articles')
                                        ->orderBy('id', 'DESC')
                                        ->get();
        $provider_orders = ProviderOrderHelper::setArticles($provider_orders);
        return response()->json(['provider_orders' => $provider_orders], 200);
    }

    function store(Request $request) {
        $provider_order = ProviderOrder::create([
            'num'         => ProviderOrderHelper::getNum(),
            'provider_id' => $request->provider_id,
            'user_id'     => $this->userId(),
        ]);
        ProviderOrderHelper::attachArticles($request->articles, $provider_order);
        ProviderOrderHelper::sendEmail($request->send_email, $provider_order);
        $provider_order = $this->getFullModel($provider_order->id);
        return response()->json(['provider_order' => $provider_order], 201);
    }

    function update(Request $request, $id) {
        $provider_order = ProviderOrder::find($id);
        ProviderOrderHelper::attachArticles($request->articles, $provider_order);
        ProviderOrderHelper::sendEmail($request->send_email, $provider_order);
        $provider_order = $this->getFullModel($provider_order->id);
        return response()->json(['provider_order' => $provider_order], 201);
    }

    function setReceived(Request $request) {
        $provider_order = ProviderOrder::find($request->provider_order_id);
        ProviderOrderHelper::updateArticleStock($provider_order, $request->article);
        $provider_order->articles()->updateExistingPivot($request->article['id'], [
                                        'received' => $request->article['received']
                                    ]);
        $article = ArticleHelper::getFullArticle($request->article['id']);
        return response()->json(['article' => $article], 200);
    }

    function pdf($id) {
        $provider_order = ProviderOrder::find($id);
        $pdf = new ProviderOrderPdf($provider_order);
    }

    function getFullModel($id) {
        $provider_order = ProviderOrder::where('id', $id)
                                        ->with('articles')
                                        ->with('provider')
                                        ->first();
        $provider_order = ProviderOrderHelper::setArticles([$provider_order])[0];
        return $provider_order;
    }
    
}
