<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Http\Controllers\Helpers\UserHelper;
use App\Notifications\OrderProductionNotification;
use App\OrderProductionStatus;
use Carbon\Carbon;

class OrderProductionHelper {

	static function setInfoFromBudget($order_productions) {
		foreach ($order_productions as $order_production) {
			if (!is_null($order_production->budget)) {
				foreach ($order_production->budget->articles as $article) {
					$order_production->articles[] = $article;
				}
			}
			if (!is_null($order_production->budget->client)) {
				$order_production->client = $order_production->budget->client;
			}
			if (!is_null($order_production->budget->start_at)) {
				$order_production->start_at = $order_production->budget->start_at;
			}
			if (!is_null($order_production->budget->finish_at)) {
				$order_production->finish_at = $order_production->budget->finish_at;
			}
			if (!is_null($order_production->budget->observations)) {
				$order_production->observations .= ' '.$order_production->budget->observations;
			}
		}
		return $order_productions;
	}

	static function attachArticles($order_production, $articles) {
		$order_production->articles()->detach();
		foreach ($articles as $article) {
			if (isset($article['pivot']['delivered'])) {
				$delivered = $article['pivot']['delivered'];
			} else {
				$delivered = null;
			}
			if ($article['status'] == 'inactive') {
				$art = Article::find($article['id']);
				$art->bar_code = $article['bar_code'];
				$art->provider_code = $article['provider_code'];
				$art->name = $article['name'];
				$art->save();
			}
			$order_production->articles()->attach($article['id'], [
											'amount' 	=> $article['pivot']['amount'],
											'price' 	=> $article['pivot']['price'],
											'bonus' 	=> $article['pivot']['bonus'],
											'location' 	=> $article['pivot']['location'],
											'delivered' => $delivered,
										]);
		}
	}

	static function getFisrtStatus() {
		$status = OrderProductionStatus::where('user_id', UserHelper::userId())
										->orderBy('position', 'ASC')
										->first();
		return $status->id;
	}

	static function sendCreatedMail($order_production, $send_mail) {
		if ($send_mail && $order_production->budget->client->email != '') {
			$subject = 'ORDEN DE PRODUCCION CREADA';
			$line = 'Empezamos a trabajar en tu pedido, actualmente se encuentra en la primer fase, nos comunicaremos por este medio para informarte sobre cualquier actualización en el estado de producción.';
			$order_production->budget->client->notify(new OrderProductionNotification($order_production, $subject, $line));
		}
	}

	static function sendUpdatedMail($order_production) {
		if (!is_null($order_production->client) && $order_production->client->email != '') {
			$subject = 'ORDEN DE PRODUCCION ACTUALIZADA';
			$line = 'Nos alegra informarte que tu pedido avanzo a la siguiente fase, nos comunicaremos por este medio para informarte sobre cualquier actualización en el estado de producción.';
			$order_production->client->notify(new OrderProductionNotification($order_production, $subject, $line));
		}
	}

}