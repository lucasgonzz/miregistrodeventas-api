<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Budget;
use App\BudgetObservation;
use App\BudgetProduct;
use App\CurrentAcount;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Notifications\BudgetCreated;
use App\OrderProduction;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BudgetHelper {

	static function getNum() {
		$last = Budget::where('user_id', UserHelper::userId())
						->orderBy('id', 'DESC')
						->first();
		return is_null($last) ? 1 : $last->num + 1; 
	}

	static function getFormatedNum($budgets) {
		foreach ($budgets as $budget) {
			$letras_faltantes = 8 - strlen($budget->num);
			$cbte_numero = '';
			for ($i=0; $i < $letras_faltantes; $i++) { 
				$cbte_numero .= '0'; 
			}
			$cbte_numero  .= $budget->num;
			$budget->num = $cbte_numero;
		}
		return $budgets;
	}

    static function getFullModel($id) {
        $budget = Budget::where('id', $id)
                        ->withAll()
                        ->first();
        $budget = Self::getFormatedNum([$budget])[0];
        return $budget;
    }

	static function sendMail($budget, $send_mail) {
		if ($send_mail == 1 && $budget->client->email != '') {
			$budget->client->notify(new BudgetCreated($budget));
		}
	}

	static function checkStatus($budget) {
		if ($budget->budget_status->name == 'Confirmado') {
			Self::deleteCurrentAcount($budget);
			Self::saveCurrentAcount($budget);
	        CurrentAcountHelper::checkSaldos('client', $budget->client_id);
	        Self::saveSale($budget);
		}
	}

	static function saveSale($budget) {
		if (is_null($budget->sale)) {
	        $sale = Sale::create([
	            'num_sale' 				=> SaleHelper::numSale(UserHelper::userId()),
	            'user_id' 				=> UserHelper::userId(),
	            'client_id' 			=> $budget->client_id,
	            'budget_id' 			=> $budget->id,
            	'employee_id'           => SaleHelper::getEmployeeId(),
	            'save_current_acount' 	=> 0,
	        ]);
		}
	}

	static function saveCurrentAcount($budget) {
		$debe = Self::getTotal($budget);
        $current_acount = CurrentAcount::create([
            'detalle'     => 'Presupuesto N°'.$budget->num,
            'debe'        => $debe,
            'status'      => 'sin_pagar',
            'client_id'   => $budget->client_id,
            'budget_id'   => $budget->id,
            'description' => null,
            'created_at'  => Carbon::now(),
        ]);
        Log::info('Se actualizo saldo a '.$debe);
        $current_acount->saldo = Numbers::redondear(CurrentAcountHelper::getSaldo('client', $budget->client_id, $current_acount) + $debe);
        $current_acount->save();
	}

	static function deleteCurrentAcount($budget) {
		$current_acount = CurrentAcount::where('budget_id', $budget->id)
										->first();
		if (!is_null($current_acount)) {
			$current_acount->delete();
			return true;
		}
		return false;
	}

	static function getTotal($budget) {
		$total = 0;
		foreach ($budget->articles as $article) {
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

	static function attachArticles($budget, $articles) {
		$budget->articles()->detach();
		foreach ($articles as $article) {
			$id = (int)$article['id'];
			$amount = $article['pivot']['amount'];
			$bonus = $article['pivot']['bonus'];
			$location = $article['pivot']['location'];
			$price = $article['pivot']['price'];
			if ($article['status'] == 'inactive' && $id > 0) {
				$art = Article::find($article['id']);
				$art->bar_code 		= $article['bar_code'];
				$art->provider_code = $article['provider_code'];
				$art->name 			= $article['name'];
				$art->save();
			}
			$budget->articles()->attach($article['id'], [
									'amount' 	=> $amount,
									'price' 	=> $price,
									'bonus' 	=> $bonus,
									'location' 	=> $location,
								]);
		}		
	}

	static function attachProducts($budget, $products) {
		$models_products = BudgetProduct::where('budget_id', $budget->id)->pluck('id');
		BudgetProduct::destroy($models_products);
		foreach ($products as $product) {
			$product = (object) $product;
			BudgetProduct::create([
				'bar_code'	=> $product->bar_code,
				'amount'	=> $product->amount,
				'name'		=> $product->name,
				'price'		=> $product->price,
				'bonus'		=> isset($product->bonus) ? $product->bonus : null,
				'location'  => isset($product->location) ? $product->location : null,
				'budget_id' => $budget->id
			]);
		}
	}

	static function attachOptionalStatuses($budget, $optional_statuses) {
		$budget->optional_order_production_statuses()->detach();
		foreach ($optional_statuses as $optional_status) {
			$budget->optional_order_production_statuses()->attach($optional_status['id']);
		}
	}

	static function attachObservations($budget, $observations) {
		$observations_models = BudgetObservation::where('budget_id', $budget->id)->pluck('id');
		BudgetObservation::destroy($observations_models);
		foreach ($observations as $observation) {
			$observation = (object) $observation;
			if ($observation->text != '') {
				BudgetObservation::create([
					'text'		=> $observation->text,
					'budget_id' => $budget->id
				]);
			}
		}
	}

}