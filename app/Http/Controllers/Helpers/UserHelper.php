<?php

namespace App\Http\Controllers\Helpers;

use App\User;
use Carbon\Carbon;

class UserHelper {

	static function userId() {
        $user = Auth()->user();
        if (is_null($user)) {
        	$user = User::where('company_name', 'Lucas')->first();
        	return $user->id;
        } else {
	        if (is_null($user->owner_id)) {
	            return $user->id;
	        } else {
	            return $user->owner_id;
	        }
        }
    }

    static function checkUserTrial($user) {
    	$expired_at = $user->expired_at;
    	if (!is_null($expired_at) && $expired_at->lte(Carbon::now())) {
    		$user->trial_expired = true;
    	} else {
    		$user->trial_expired = false;
    	}
    	return $user;
    }

	static function isOscar() {
        $user = Auth()->user();
        if (is_null($user)) {
        	return false;
        } else {
        	if (env('APP_ENV') == 'local') {
	        	return $user->id == 303;
        	} else {
	        	return $user->id == 2;
        	}
        }
    }
}