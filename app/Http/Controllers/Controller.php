<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function userId() {
        $user = Auth()->user();
        if (is_null($user->owner_id)) {
            return $user->id;
        } else {
            return $user->owner_id;
        }
    }

    function isProvider() {
        $user = Auth()->user();
        if ($user->hasRole('provider')) {
            return true;
        }
        return false;
    }

    // static function userId() {
    //     $user = Auth()->user();
    //     if (is_null($user->owner_id)) {
    //         return $user->id;
    //     } else {
    //         return $user->owner_id;
    //     }
    // }

    function isCompanyNameRepeated($company_name) {
        $users = User::whereNull('owner_id')->get();
        $repeated = false;
        foreach ($users as $user) {
            if ($user->company_name == ucwords($company_name)) {
            	$repeated = true;
            }
        }
        return $repeated;
    }
}
