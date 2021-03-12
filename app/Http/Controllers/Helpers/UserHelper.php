<?php

namespace App\Http\Controllers\Helpers;

class UserHelper {
	static function userId() {
        $user = Auth()->user();
        if (is_null($user)) {
        	return 1;
        } else {
	        if (is_null($user->owner_id)) {
	            return $user->id;
	        } else {
	            return $user->owner_id;
	        }
        }
    }
}