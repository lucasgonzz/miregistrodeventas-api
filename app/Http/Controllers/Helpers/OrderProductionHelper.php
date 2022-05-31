<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\OrderProductionStatus;
use Carbon\Carbon;

class OrderProductionHelper {

	static function getStatusId($name) {
		$status = OrderProductionStatus::where('name', $name)->first();
		return $status->id;
	}

}