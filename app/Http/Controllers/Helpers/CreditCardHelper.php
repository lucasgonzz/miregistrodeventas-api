<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\CreditCardPaymentPlan;
use Carbon\Carbon;

class CreditCardHelper {

	static function attachCreditCardPaymentPlans($model, $credit_card_payment_plans) {
		foreach ($credit_card_payment_plans as $payment_plan) {
			if (!isset($payment_plan['id'])) {
				$_price_list = CreditCardPaymentPlan::create([
					'credit_card_id' => $model->id,
				]);
			} else {
				$_price_list = CreditCardPaymentPlan::find($payment_plan['id']);
			}
			$_price_list->installments  = $payment_plan['installments'];
			$_price_list->surchage 		= $payment_plan['surchage'];
			$_price_list->save();
		}
	}
	
}