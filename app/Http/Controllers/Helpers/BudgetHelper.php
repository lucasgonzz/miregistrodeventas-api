<?php

namespace App\Http\Controllers\Helpers;

use App\Budget;
use App\BudgetObservation;
use App\BudgetProduct;
use App\Http\Controllers\Helpers\UserHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BudgetHelper {

	static function getNum() {
		$last = Budget::where('user_id', UserHelper::userId())
						->orderBy('id', 'DESC')
						->first();
		return is_null($last) ? 1 : $last->num + 1; 
	}

	static function attachProducts($budget, $products) {
		$models_products = BudgetProduct::where('budget_id', $budget->id)->pluck('id');
		BudgetProduct::destroy($models_products);
		Log::info($products);
		foreach ($products as $product) {
			$product = (object) $product;
			Log::info($product->name);
			BudgetProduct::create([
				'code'		=> $product->code,
				'amount'	=> $product->amount,
				'name'		=> $product->name,
				'price'		=> $product->price,
				'bonus'		=> $product->bonus,
				'budget_id' => $budget->id
			]);
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