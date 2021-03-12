<?php

namespace App\Http\Controllers\Helpers;

class Numbers {


	static function percentage($p) {
		$percentage_card = (float)$p;
		if ($percentage_card < 10) {
			return '0.0'.$percentage_card;
		} 
		return '0.'.$percentage_card;
	}

	static function price($price) {
		$pos = strpos($price, '.');
		if ($pos != false) {
			$centavos = explode('.', $price)[1];
			$new_price = explode('.', $price)[0];
			if ($centavos != '00') {
				$new_price += ".$centavos";
				return number_format($new_price, 2, ',', '.');
			} else {
				return number_format($new_price, 0, '', '.');			
			}
		} else {
			return number_format($price, 0, '', '.');
		}
	}
}