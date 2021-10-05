<?php

namespace App\Http\Controllers\Helpers;

use App\User;

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
}