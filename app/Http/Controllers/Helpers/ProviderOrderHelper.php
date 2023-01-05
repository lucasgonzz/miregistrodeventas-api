<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\CurrentAcount;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Notifications\ProviderOrderCreated;
use App\Notifications\UpdatedArticle;
use App\ProviderOrder;
use App\ProviderOrderAfipTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProviderOrderHelper {

	static function getNum($user_id = null) {
		if (is_null($user_id)) {
			$user_id = UserHelper::userId();
		}
		$last = ProviderOrder::where('user_id', $user_id)
								->orderBy('id', 'DESC')
								->first();
		return is_null($last) ? 1 : $last->num + 1;
	}

	static function deleteCurrentAcount($provider_order) {
		$current_acount = CurrentAcount::where('provider_order_id', $provider_order->id)->first();
		if (!is_null($current_acount)) {
			$current_acount->delete(); 
			CurrentAcountHelper::updateProviderSaldos($current_acount);
		}
	}

	static function sendEmail($send_email, $provider_order) {
		if ($send_email && !is_null($provider_order->provider->email)) {
			$provider_order->provider->notify(new ProviderOrderCreated($provider_order));
		}
	}

	static function updateArticleStock($_article, $last_received, $provider_order) {
		if ($_article['pivot']['received'] > 0) {
			$article = Article::find($_article['id']);
			if (is_null($article->stock)) {
				$article->stock = 0;
			}
			if ($_article['pivot']['update_cost']) {
				if ($_article['pivot']['received_cost'] != '') {
					$article->cost = $_article['pivot']['received_cost'];
				} else if ($_article['pivot']['cost'] != '') {
					$article->cost = $_article['pivot']['cost'];
				}
			}
			if (!is_null($_article['pivot']['iva_id']) && $_article['pivot']['iva_id'] != 0) {
				$article->iva_id = $_article['pivot']['iva_id'];
			}
			if (isset($last_received[$article->id])) {
				$article->stock -= $last_received[$article->id];
			}
			$article->stock += $_article['pivot']['received'];
			if ($article->status == 'inactive') {
				$article->status = 'active';
				$article->apply_provider_percentage_gain = 1;
				$article->created_at = Carbon::now();
			}
			$cant_providers = count($article->providers);
			if ($cant_providers == 0 || ($cant_providers >= 1 && $article->providers[$cant_providers-1]->id != $provider_order->provider_id)) {
				$article->providers()->attach($provider_order->provider_id, [
										'amount' => $_article['pivot']['received'],
										'cost' 	 => $_article['pivot']['cost'],
									]);
			}
			$article->save();
        	$article->user->notify(new UpdatedArticle($article));
		}
	}

	static function attachArticles($articles, $provider_order) {
		$last_received = Self::getLastReceived($provider_order);
		// Self::deleteInactiveArticles($provider_order);
		$provider_order->articles()->sync([]);
		foreach ($articles as $article) {
			if ($article['status'] == 'inactive') {
				$art = Article::find($article['id']);
				if (!is_null($art)) {
					$art->bar_code = $article['bar_code'];
					$art->provider_code = $article['provider_code'];
					$art->name = $article['name'];
					$art->save();
				}
			} 
			$provider_order->articles()->attach($article['id'], [
											'amount' 		=> $article['pivot']['amount'],
											'notes' 		=> $article['pivot']['notes'],
											'received' 		=> $article['pivot']['received'],
											'cost' 			=> $article['pivot']['cost'],
											'received_cost' => $article['pivot']['received_cost'],
											'update_cost' 	=> $article['pivot']['update_cost'],
											'iva_id'    	=> Self::getIvaId($article),
										]);
			Self::updateArticleStock($article, $last_received, $provider_order);
		}
		Self::saveCurrentAcount($provider_order);
	}

	static function deleteInactiveArticles($provider_order) {
		foreach ($provider_order->articles as $article) {
			if ($article->status == 'inactive') {
				$article->delete();
			}
		}
	}

	static function getIvaId($article) {
		if ($article['status'] == 'active' && $article['pivot']['iva_id'] == 0) {
			return $article['iva_id'];
		}
		return $article['pivot']['iva_id'];
	}

	static function attachAfipTickets($afip_tickets, $model) {
		foreach ($afip_tickets as $afip_ticket) {
			$_afip_ticket = null;
			if (!isset($afip_ticket['id']) && ($afip_ticket['code'] != '' || $afip_ticket['issued_at'] != '' || $afip_ticket['total'] != '')) {
				$_afip_ticket = ProviderOrderAfipTicket::create([
					'provider_order_id' => $model->id,
				]);
			} else if (isset($afip_ticket['id'])) {
				$_afip_ticket = ProviderOrderAfipTicket::find($afip_ticket['id']);
			}
			if (!is_null($_afip_ticket)) {
				$_afip_ticket->issued_at 	= $afip_ticket['issued_at'];
				$_afip_ticket->code 		= $afip_ticket['code'];
				$_afip_ticket->total 		= $afip_ticket['total'];
				$_afip_ticket->save();
			}
		}
	}

	static function saveCurrentAcount($provider_order) {
		$total = Self::getTotal($provider_order->id);
		if ($total > 0) {
			$current_acount = CurrentAcount::where('provider_order_id', $provider_order->id)->first();
			if (is_null($current_acount)) {
				$current_acount = CurrentAcount::create([
					'detalle' 			=> 'Pedido N° '.$provider_order->num,
					'debe'				=> $total,
					'status' 			=> 'sin_pagar',
					'user_id'			=> UserHelper::userId(),
					'provider_id'		=> $provider_order->provider_id,
					'provider_order_id'	=> $provider_order->id,
				]);
				$current_acount->saldo = CurrentAcountHelper::getProviderSaldo($current_acount) + $total;
				$current_acount->save();
				Log::info('Se creo current_acount con saldo de: '.$current_acount->saldo);
			} else if ($current_acount->debe != $total) {
				$current_acount->debe = $total;
				$current_acount->saldo = CurrentAcountHelper::getProviderSaldo($current_acount) + $total;
				$current_acount->save();
				CurrentAcountHelper::updateProviderSaldos($current_acount);
				Log::info('Se actualizo current_acount con saldo de: '.$current_acount->saldo);
			}
		}
	}

	static function getTotal($id) {
		$provider_order = ProviderOrder::find($id);
		$total = 0;
		foreach ($provider_order->articles as $article) {
			if ($article->pivot->cost != '' && $article->pivot->received > 0) {
				$total_article = $article->pivot->cost * $article->pivot->received;
				if ($provider_order->total_with_iva && !is_null($article->pivot->iva_id)) {
					$ct = new Controller();
					$iva = $ct->getModelBy('ivas', 'id', $article->pivot->iva_id);
					$total_article += $total_article * $iva->percentage / 100;
				}
				$total += $total_article;
			}
		}
		return $total;
	}

	static function getLastReceived($provider_order) {
		$last_received = [];
		foreach ($provider_order->articles as $article) {
			$last_received[$article->id] = $article->pivot->received;
		}
		return $last_received;
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