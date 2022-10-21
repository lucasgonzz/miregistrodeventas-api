<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Http\Controllers\Helpers\OrderProductionRecipe;
use App\Http\Controllers\Helpers\UserHelper;
use App\Notifications\OrderProductionNotification;
use App\OrderProduction;
use App\OrderProductionStatus;
use Carbon\Carbon;

class OrderProductionHelper {

	static function setArticles($order_productions) {
		foreach ($order_productions as $order_production) {
			foreach ($order_production->articles as $article) {
				foreach (Self::getStatuses() as $status) {
					$article->pivot->{'order_production_status_'.$status->id} = Self::getArticleFinishedAmount($order_production, $article, $status);  
				}
			}
		}
		return $order_productions;
	} 

	static function getArticleFinishedAmount($order_production, $article, $status) {
		$article_finished_res = null;
		foreach ($order_production->articles_finished as $article_finished) {
			if ($article_finished->id == $article->id && $article_finished->pivot->order_production_status_id == $status->id) {
				$article_finished_res = $article_finished;
				break;
			}
		}
		if (!is_null($article_finished_res)) {
			return $article_finished_res->pivot->amount;
		}
		return 0;
	}

	static function attachArticles($order_production, $articles) {
		$cantidades_actuales = OrderProductionRecipe::getCantidadesActuales($order_production);
		$order_production->articles()->detach();
		$order_production->articles_finished()->detach();
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
		  	$order_production_statuses = Self::getStatuses();
		  	foreach ($order_production_statuses as $status) {
		  		if (isset($article['pivot']['order_production_status_'.$status->id])) {
				  	$order_production->articles_finished()->attach($article['id'], [
				  									'order_production_status_id' => $status->id,
				  									'amount' 					 => $article['pivot']['order_production_status_'.$status->id]
				  								]);
		  		}
		  	}
		}
		$order_production = OrderProduction::find($order_production->id);
		OrderProductionRecipe::checkRecipes($order_production, $cantidades_actuales);
	}

	static function getTotal($order_production) {
		$total = 0;
		foreach ($order_production->articles as $article) {
			$total += Self::totalArticle($article);
		}
		return $total;
	}

	static function totalArticle($article) {
		$total = $article->pivot->price * $article->pivot->amount;
		if (!is_null($article->pivot->bonus)) {
			$total -= $total * (float)$article->pivot->bonus / 100;
		}
		return $total;
	}

	static function getStatuses() {
	  	return OrderProductionStatus::where('user_id', UserHelper::userId())
									->whereNotNull('position')
									->orderBy('position', 'ASC')
									->get();
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