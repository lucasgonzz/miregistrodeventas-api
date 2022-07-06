<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Notifications\ProviderOrderCreated;
use App\ProviderOrder;
use Carbon\Carbon;

class ProviderOrderHelper {

	static function getNum() {
		$last = ProviderOrder::where('user_id', UserHelper::userId())
								->orderBy('id', 'DESC')
								->first();
		return is_null($last) ? 1 : $last->num + 1;
	}

	static function sendEmail($send_email, $provider_order) {
		if ($send_email && !is_null($provider_order->provider->email)) {
			$provider_order->provider->notify(new ProviderOrderCreated($provider_order));
		}
	}

	static function updateArticleStock($provider_order, $_article) {
		$article = Article::find($_article['id']);
		if (!is_null($article->stock)) {
			foreach ($provider_order->articles as $art) {
				if ($art->id == $article->id) {
					$last_received = $art->pivot->received;
				}
			}
			$article->stock -= $last_received;
			$article->stock += $_article['received'];
			$article->timestamps = false;
			$article->save();
		}
	}

	static function attachArticles($articles, $provider_order) {
		$provider_order->articles()->sync([]);
		foreach ($articles as $article) {
			$amount = $article['amount'];
			$notes = isset($article['notes']) ? $article['notes'] : null;
			$received = isset($article['pivot']['received']) ? $article['pivot']['received'] : 0;
			if (isset($article['from_provider_order']) && $article['from_provider_order']) {
				$article = Article::create([
					'name'	 	=> $article['name'],
        			'slug'   	=> ArticleHelper::slug($article['name']),
					'status' 	=> 'from_provider_order',
					'user_id'	=> UserHelper::userId(),
				]);
			} else {
				$article = (object)$article;
			}
			$provider_order->articles()->attach($article->id, [
											'amount' 	=> $amount,
											'notes' 	=> $notes,
											'received' 	=> $received,
										]);
		}
	}

	static function setArticles($provider_orders) {
		foreach ($provider_orders as $provider_order) {
			foreach ($provider_order->articles as $article) {
				$article->amount = $article->pivot->amount;
				$article->notes = $article->pivot->notes;
				$article->received = $article->pivot->received;
			}
		}
		return $provider_orders;
	}

}