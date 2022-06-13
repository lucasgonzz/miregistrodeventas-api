<?php

namespace App\Http\Controllers\Helpers;

use App\Budget;
use App\BudgetObservation;
use App\BudgetProduct;
use App\CurrentAcount;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use App\Notifications\BudgetCreated;
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
                        ->with('client.iva_condition')
                        ->with('products')
                        ->with('observations')
                        ->with('order_production.status')
                        ->first();
        $budget = Self::getFormatedNum([$budget])[0];
        return $budget;
    }

	static function sendMail($budget, $send_mail) {
		if ($send_mail == 1 && $budget->client->email != '') {
			$budget->client->notify(new BudgetCreated($budget));
		}
	}

	static function saveCurrentAcount($budget) {
		$debe = Self::getTotal($budget);
        $current_acount = CurrentAcount::create([
            'detalle'     => 'Presupuesto '.$budget->num,
            'debe'        => $debe,
            'status'      => 'sin_pagar',
            'client_id'   => $budget->client_id,
            'budget_id'   => $budget->id,
            'description' => null,
        ]);
        $current_acount->saldo = Numbers::redondear(CurrentAcountHelper::getSaldo($budget->client_id, $current_acount) + $debe);
        $current_acount->save();
	}

	static function getTotal($budget) {
		$total = 0;
		foreach ($budget->products as $product) {
			$total += Self::totalProduct($product);
		}
		return $total;
	}

	static function totalProduct($product) {
		$total = $product->price * $product->amount;
		if (!is_null($product->bonus)) {
			$total -= $total * (float)$product->bonus / 100;
		}
		return $total;
	}

	static function attachProducts($budget, $products) {
		$models_products = BudgetProduct::where('budget_id', $budget->id)->pluck('id');
		BudgetProduct::destroy($models_products);
		Log::info($products);
		foreach ($products as $product) {
			$product = (object) $product;
			Log::info($product->name);
			BudgetProduct::create([
				'bar_code'	=> $product->bar_code,
				'amount'	=> $product->amount,
				'name'		=> $product->name,
				'price'		=> $product->price,
				'bonus'		=> isset($product->bonus) ? $product->bonus : null,
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