<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Http\Controllers\Helpers\ProviderOrderHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Provider;
use App\ProviderOrder;
use Carbon\Carbon;

class OrderProductionProviderOrderHelper {

	static function createProviderOrder($order_production) {
		$client_comercio_city = $order_production->client->comercio_city_user;
		$provider_from_client = Provider::where('user_id', $client_comercio_city->id)
										->where('comercio_city_user_id', UserHelper::userId())
										->first();
		if (!is_null($provider_from_client)) {
	        $provider_order = ProviderOrder::create([
	            'num'         => ProviderOrderHelper::getNum($client_comercio_city->id),
	            'provider_id' => $provider_from_client->id,
	            'user_id'     => $client_comercio_city->id,
	        ]);
	        Self::attachArticles($order_production, $provider_order, $client_comercio_city);
		}
	}

	static function attachArticles($order_production, $provider_order, $client_comercio_city) {
		foreach ($order_production->articles as $article) {
			$article_from_client = Self::getArticleFromClient($client_comercio_city, $article);
			if (is_null($article_from_client)) {
				$article_from_client = Article::create([
					'user_id' => $client_comercio_city->id,
					'status'  => 'inactive',
					'name'	  => $article->name,
				]);						
			} 
			$provider_order->articles()->attach($article_from_client->id, [
											'amount' => $article->pivot->amount,
											'cost'   => Self::getArticlePrice($article),
										]);
		}
	}

	static function getArticlePrice($article) {
		$price = $article->pivot->price;
		if (!is_null($article->pivot->bonus)) {
			$price = $price - ($price * $article->pivot->bonus / 100);
		}
		return $price;
	}

	static function getArticleFromClient($client_comercio_city, $article) {
		if ($article->bar_code != '') {
			$_article = Article::where('bar_code', $article->bar_code);
		} else if ($article->provider_code != '') {
			$_article = Article::where('provider_code', $article->provider_code);
		} else {
			$_article = Article::where('name', $article->name);
		}
		$_article = $_article->where('user_id', $client_comercio_city->id)
							->first();
		return $_article;
	}
	
}