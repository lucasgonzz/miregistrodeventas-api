<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Http\Controllers\Helpers\TwilioHelper;
use Carbon\Carbon;

class CuponHelper {
	static function getAmount($request) {
		if ($request['percentage'] == '') {
			return $request['amount'];
		}
		return null;
	}

	static function getPercentage($request) {
		if ($request['amount'] == '') {
			return $request['percentage'];
		}
		return null;
	}

	static function getExpirationDate($request) {
		if ($request['expiration_date'] != '') {
			return $request['expiration_date'];
		}
		return null;
	}

	static function sendCuponNotification($cupon) {
		if (!is_null($cupon->amount)) {
        	$message = 'Tenes un descuento de $'.$cupon->amount;
		} else {
        	$message = 'Tenes un descuento del '.$cupon->percentage.'%';
		}
        $title = '¡Te regalamos un cupon!';
        TwilioHelper::sendNotification($cupon->buyer_id, $title, $message);
	}

}