<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\AuthHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    use AuthenticatesUsers;

    public function logout(Request $request) {
        Auth::logout();
    }

    public function login(Request $request) {
        if (AuthHelper::isFromOwner($request)) {
            if (AuthHelper::loginLucas($request)) {
                return response()->json([
                    'login' => true,
                    'user'  => UserHelper::getFullModel(),
                ], 200);
            } else {
                if (Auth::attempt(['company_name' => $request->company_name, 
                                    'password' => $request->password,
                                    'status' => 'commerce'], $request->remember)) {
                    $user = UserHelper::getFullModel();
                    $user = UserHelper::checkUserTrial();
                    return response()->json([
                        'login' => true,
                        'user'  => $user
                    ], 200);
                } else {
                    return response()->json([
                        'login' => false,
                    ], 200);
                }
            }
            
        } else {
            if (Auth::attempt([
                                'dni' => $request->dni, 
                                'password' => $request->password, 
                            ], $request->remember)) {
                $user = UserHelper::getFullModel($this->userId(false));
                $user = UserHelper::checkUserTrial($user);
                return response()->json([
                    'login' => true,
                    'user'  => $user
                ]);
            } else {
                return response()->json([
                    'login' => false
                ]);
            }
        }
    }

    public function loginSuper(Request $request) {
        if (Auth::attempt(['name' => $request->company_name, 
                            'password' => $request->password,
                            'status' => 'super'], $request->remember)) {
            $user = User::find(Auth::user()->id);
            return response()->json([
                'login' => true,
                'user'  => $user,
            ], 200);
        } else {
            return response()->json(['login' => false], 200);
        }
    }

    public function loginEmployee(Request $request) {
        // return $request->commerce;
    }

    public function loginAdmin(Request $request) {
        // return $request->commerce;
        if (Auth::attempt([
                            'name' => $request->name, 
                            'password' => $request->password,
                            'status' => 'admin', 
                        ])) {
            return [
                'login' => true,
                'super' => false,
                'user'  => Auth::user()
            ];
        } else {
            if (Auth::attempt([
                            'name' => $request->name, 
                            'password' => $request->password,
                            'status' => 'super', 
                        ])) {
                return [
                    'login' => true,
                    'super' => true,
                    'user'  => Auth::user()
                ];
            } else {
                return [
                    'login' => false
                ];
            }
        }
    }
}