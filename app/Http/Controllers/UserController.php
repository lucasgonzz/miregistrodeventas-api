<?php

namespace App\Http\Controllers;

use App\User;
use App\Recommendation;
use App\Collection;
use Carbon\Carbon;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    // Configuracion
    function linkRecommended() {
        $user = Auth()->user();
        $recommendations = Recommendation::where('commerce_id', $user->id)
                                        ->get();

        $num_recommended = (count($recommendations) >= 1 ? count($recommendations) : 0) + 1;

        // $link = Str::slug('hola me llañño lucas') . '/' . $num_recommended;
        $link = Str::slug($user->company_name) . '/' . $num_recommended;
        return [
            'link' => $link,
        ];  
    }

    function collections() {
        return Collection::where('commerce_id', Auth()->user()->id)
                        ->get();
    }

    // Confirguracion - Editar
    function update(Request $request) {
        $user = Auth()->user();
        $user->name = ucwords($request->name);
        $users = User::where('id', '!=', $user->id)->get();
        $repeated_company_name = false;
        if ($user->company_name != $request->company_name) {
            foreach ($users as $user_) {
                if ($user_->company_name == ucwords($request->company_name)) {
                    $repeated_company_name = true;
                }
            }
        }
        if (!$repeated_company_name) {
            $user->company_name = ucwords($request->company_name);
        } else {
            return [
                'repeated' => true,
            ];
        }
        $user->save();
        return [
            'repeated' => false,
        ];
    }

    function updateImage(Request $request) {
        $user = Auth()->user();
        $upload_path = public_path('images/users/'.$user->id);
        $time = time();
        $extension = $request->image->getClientOriginalExtension();
        $generated_new_name = $time . '.' . $extension;
        $request->image->move($upload_path, $generated_new_name);
        $user->image = $generated_new_name;
        $user->save();
    }

    function ownerByCompanyName($company_name) {
        return User::where('company_name', str_replace('-', ' ', $company_name))
                    ->first();
    }

    function admin($admin_id) {
        return User::where('status', 'admin')
                    ->where('id', $admin_id)
                    ->first();
    }

    function index($id = null) {
        if (is_null($id)) {
            return User::where('id', Auth()->user()->id)
                        ->with('permissions')
                        ->with('roles')
                        ->first();
        } else {
            return User::where('id', $id)
                        ->with('permissions')
                        ->with('roles')
                        ->first();
        }
    }

    function contratarServicio() {
        $user = Auth()->user();
        $user->status = 'in_use';
        $user->created_at = Carbon::now();
        // $expire = new Carbon($user->expire);
        // $user->expire = $expire->addMonth();
        $user->save();
    }

	function getEmployees() {
    	$user = Auth()->user();
		return User::where('owner_id', $user->id)
                    ->with('permissions')
                    ->get();
	}      

    function updateEmployeePermissions($employee_id, Request $request) {
        $employee = User::where('id', $employee_id)
                        ->with('permissions')
                        ->first();
        $owner = User::where('id', $employee->owner_id)
                        ->with('permissions')
                        ->first();

        // Se obtiene los permisos para usar partes de la app del dueño
        $permissions_can_use = [];
        foreach ($owner->permissions as $permission) {
            if ($permission->user_id == 0) {
                $permissions_can_use[] = $permission->id;
            }
        }

        // Se le actualizan los articulos que le da el dueño
        $employee->permissions()->sync($permissions_can_use);
        $employee->permissions()->attach($request->permissions);
        return $request->permissions;
    }

	function deleteEmployee($id) {
		$user = User::find($id);
        $user->roles()->detach();
        $user->permissions()->detach();
        $user->delete();
	}

    function saveEmployee($name, $password, $permissions) {
        $permissions = explode('-', $permissions);
    	$user = Auth()->user();
        $employee = User::where('owner_id', $user->id)
                            ->where('name', $name)
                            ->first();


        if (is_null($employee)) {
        	$employee = User::create([
                'name' => ucwords($name),
        		'company_name' => $user->company_name,
        		'password' => Hash::make($password),
                'owner_id' => $user->id,
                'admin_id' => $user->admin_id,
        		'percentage_card' => $user->percentage_card,
                'created_at' => Carbon::now(),
        	]);

        	if ($user->hasRole('provider')) {
                $employee->syncRoles('provider');
        	} else {
                $employee->syncRoles('commerce');
        	}
            $permissions_can_use = [];
            foreach ($user->permissions as $permission) {
                $permissions_can_use[] = $permission->id;
            }
            $employee->permissions()->attach($permissions_can_use);
        	$employee->permissions()->attach($permissions);
            return ['repeated' => false];
        } else {
            return ['repeated' => true];
        }
    }

    function getCompanyName() {
        return Auth()->user()->company_name;
    }

    function setCompanyName($company_name) {
        $user_id = Auth()->user()->id;
        $user = User::find($user_id);
        $user->company_name = Str::slug($company_name);
        $user->save();
        foreach ($user->employees as $employee) {
            $employee->company_name = $company_name;
            $employee->save();
        }
    }

    function getPercentageCard() {
        return Auth()->user()->percentage_card;
    }

    function setPercentageCard($percentage_card) {
        $user_id = Auth()->user()->id;
        $user = User::find($user_id);
        $user->percentage_card = $percentage_card;
        $user->save();
        foreach ($user->employees as $employee) {
            $employee->percentage_card = $percentage_card;
            $employee->save();
        }
    }

    public function password() {
        return view('auth.password');
    }

    public function updatePassword(Request $request) {

        if (Hash::check($request->current_password, Auth()->user()->password)) {
            $user = User::find(Auth()->user()->id);
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);
            return response('ok');
        } else {
            return response('no');
        }
    }
}
