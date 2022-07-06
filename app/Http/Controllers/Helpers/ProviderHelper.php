<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Helpers\UserHelper;
use App\Provider;
use Carbon\Carbon;

class ProviderHelper {

	static function getProvider($name) {
		$provider = Provider::where('name', $name)
							->where('user_id', UserHelper::userId())
							->first();
		return $provider;
	}

}