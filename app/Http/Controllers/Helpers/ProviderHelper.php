<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Helpers\UserHelper;
use App\Provider;
use App\ProviderPriceList;
use Carbon\Carbon;

class ProviderHelper {

	static function attachProviderPriceLists($model, $provider_price_lists) {
		foreach ($provider_price_lists as $prices_list) {
			$_price_list = null;
			if (!isset($prices_list['id']) && ($prices_list['name'] != '' || $prices_list['percentage'] != '')) {
				$_price_list = ProviderPriceList::create([
					'provider_id' => $model->id,
				]);
			} else if (isset($prices_list['id'])) {
				$_price_list = ProviderPriceList::find($prices_list['id']);
			}
			if (!is_null($_price_list)) {
				$_price_list->name 			= $prices_list['name'];
				$_price_list->percentage 	= $prices_list['percentage'];
				$_price_list->save();
			}
		}
	}

}