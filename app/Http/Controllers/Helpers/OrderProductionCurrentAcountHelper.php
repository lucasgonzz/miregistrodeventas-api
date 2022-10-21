<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\CurrentAcount;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\OrderProductionHelper;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Sale;
use Carbon\Carbon;

class OrderProductionCurrentAcountHelper {

	static function saveCurrentAcount($order_production) {
		Self::deleteCurrentAcount($order_production);
		Self::createCurrentAcount($order_production);
        CurrentAcountHelper::checkSaldos('client', $order_production->client_id);
        Self::saveSale($order_production);
	}

	static function deleteCurrentAcount($order_production) {
		$current_acount = CurrentAcount::where('order_production_id', $order_production->id)
										->first();
		if (!is_null($current_acount)) {
			$current_acount->delete();
			return true;
		}
		return false;
	}

	static function createCurrentAcount($order_production) {
		$debe = OrderProductionHelper::getTotal($order_production);
        $current_acount = CurrentAcount::create([
            'detalle'     			=> 'Orden de produccion N°'.$order_production->num,
            'debe'        			=> $debe,
            'status'      			=> 'sin_pagar',
            'client_id'   			=> $order_production->client_id,
            'order_production_id'  	=> $order_production->id,
            'description' 			=> null,
            'created_at'  			=> Carbon::now(),
        ]);
        $current_acount->saldo = Numbers::redondear(CurrentAcountHelper::getSaldo('client', $order_production->client_id, $current_acount) + $debe);
        $current_acount->save();
	}

	static function saveSale($order_production) {
		if (is_null($order_production->sale)) {
	        $sale = Sale::create([
	            'num_sale' 				=> SaleHelper::numSale(UserHelper::userId()),
	            'user_id' 				=> UserHelper::userId(),
	            'client_id' 			=> $order_production->client_id,
	            'order_production_id'   => $order_production->id,
            	'employee_id'           => SaleHelper::getEmployeeId(),
	            'save_current_acount' 	=> 0,
	        ]);
		}
	}
	
}