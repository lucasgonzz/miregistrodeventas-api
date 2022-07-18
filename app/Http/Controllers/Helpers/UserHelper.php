<?php

namespace App\Http\Controllers\Helpers;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserHelper {

	static function userId($from_owner = true) {
        $user = Auth()->user();
        if (is_null($user) && env('APP_ENV') == 'local') {
            $user = User::where('company_name', 'kas aberturas')->first();
            return $user->id;
        }
        if ($from_owner) {
            if (is_null($user->owner_id)) {
                return $user->id;
            } else {
    	        return $user->owner_id;
            }
        } else {
            return $user->id;
        }
    }

    static function user() {
        return User::find(Self::userId());
    }

    static function getFullModel($id = null) {
        if (is_null($id)) {
            $id = Self::userId();
        }
        $user = User::where('id', $id)
                    ->withAll()
                    ->first();
        return $user;
    }

    static function checkUserTrial($user = null) {
        if (is_null($user)) {
            $user = Self::getFullModel();
        }
    	$expired_at = $user->expired_at;
    	if (!is_null($expired_at) && $expired_at->lte(Carbon::now())) {
    		$user->trial_expired = true;
    	} else {
    		$user->trial_expired = false;
    	}
    	return $user;
    }

    static function setEmployeeExtencions($employee) {
        $user_owner = Self::getFullModel(); 
        $employee->owner_extencions = $user_owner->extencions;
        return $employee;
    }

	static function isOscar() {
        $user = Auth()->user();
        if (is_null($user)) {
        	return false;
        } else {
        	if (env('APP_ENV') == 'local') {
	        	return $user->id == 308;
        	} else {
	        	return $user->id == 2;
        	}
        }
    }
}