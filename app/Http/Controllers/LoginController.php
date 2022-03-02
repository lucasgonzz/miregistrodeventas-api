<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\UserHelper;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
    }

    public function login(Request $request) {
        if ($request->dni == '') {
            if (Auth::attempt(['company_name' => $request->company_name, 
                                'password' => $request->password,
                                'status' => 'commerce'], $request->remember)) {
                $user = User::where('id', Auth::user()->id)
                                ->with('plan.permissions')
                                ->with('addresses')
                                ->first();
                return response()->json([
                    'login' => true,
                    'user'  => $user
                ], 200);
            } else {
                return response()->json([
                    'login' => false,
                ], 200);
            }
        } else {
            if (Auth::attempt([
                                'dni' => $request->dni, 
                                'password' => $request->password, 
                            ], $request->remember)) {
                $user = User::where('id', Auth::user()->id)
                                ->with('permissions')
                                ->first();
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