<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\User;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function userId($from_owner = true, $user_id = null) {
        if (!is_null($user_id)) {
            return $user_id;
        }
        $user = Auth()->user();
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

    function user() {
        return Auth()->user();
    }

    function getModelBy($table, $prop_name, $prop_value, $from_user = false, $prop_to_return = null, $return_0 = false) {
        $model = DB::table($table)
                    ->where($prop_name, $prop_value);
        if ($from_user) {
            $model = $model->where('user_id', $this->userId());
        }
        $model = $model->first();
        if (!is_null($model) && !is_null($prop_to_return)) {
            return $model->{$prop_to_return};
        } 
        if ($return_0) {
            return 0;
        }
        return $model;
    }

    function num($table, $user_id = null) {
        $last = DB::table($table)
                    ->where('user_id', $this->userId(true, $user_id))
                    ->orderBy('id', 'DESC')
                    ->first();
        if (is_null($last) || is_null($last->num)) {
            return 1;
        }
        return $last->num + 1;
    }

    function createIfNotExist($table, $prop_name, $prop_value, $data_to_insert, $from_user = true) {
        $model = DB::table($table)
                    ->where($prop_name, $prop_value);
        if ($from_user) {
            $model = $model->where('user_id', $this->userId());
        }
        $model = $model->first();
        if (is_null($model)) {
            DB::table($table)->insert($data_to_insert);
        }
    }

    function isProvider() {
        $user = auth()->user();
        if (!is_null($user)) {
            return $user->type == 'provider';
        } else {
            return true;
        }
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
