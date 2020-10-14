<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Caffeinated\Shinobi\Models\Permission;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }


    function registerCommerce(Request $request) {
        if (!$this->isCompanyNameRepeated($request->company_name)) {
            $admin = User::where(['name' => 'lucas', 'status' => 'admin'])->first();
            $commerce = User::create([  
                'name' => ucwords($request->name),
                'company_name' => ucwords($request->company_name),
                'status' => 'trial',
                'admin_id' => $admin->id,
                'expire' => Carbon::now()->addWeeks(2),
                'password' => bcrypt($request->password),
            ]);
            // 1 es el rol de owner, 3 el de comercio
            $commerce->roles()->sync([1, 3]);
            $permissions_can_use = Permission::where('user_id', 0)
                                                ->get();
            foreach ($permissions_can_use as $permission) {
                $commerce->permissions()->attach($permission->id);
            }
            return response()->json(['repeated' => false]);
        } else {
            return response()->json(['repeated' => true, 'rta' => $request->company_name]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
