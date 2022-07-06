<?php

namespace App\Http\Controllers\Helpers;
use Illuminate\Support\Facades\Log;

class Numbers {


	static function percentage($p) {
		if (substr($p, strpos($p, '.'), strlen($p)) != '.00') {
			Log::info('entro con: '.$p);
			$p = str_replace('.', '', $p);
			Log::info('sale con: '.$p);
		} 
		$percentage = (float)$p;
		if ($percentage < 10) {
			return '0.0'.$percentage;
		} else if ($percentage < 100) {
			return '0.'.$percentage;
		} else if ($percentage >= 100) {
			return substr($percentage, 0, 1).'.'.substr($percentage, 1, strlen($percentage));
		}
	}

    static function redondear($num) {
        return round($num, 2, PHP_ROUND_HALF_UP);
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