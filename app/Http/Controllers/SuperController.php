<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Collection;
use Carbon\Carbon;
use Caffeinated\Shinobi\Models\Permission;

class SuperController extends Controller
{
    function admins() {
    	return User::where('status', 'admin')
    				->with('commerces')
    				->get();
    }

    function registerCommerce(Request $request) {
        $commerce = User::create([
            'name' => 'Comercio',
            'company_name' => ucwords($request->company_name),
            'admin_id' => $request->admin_id,
            'status' => 'for_trial',
            'password' => bcrypt($request->password),
        ]);
        // 1 es el rol de owner, 3 el de comercio
        $commerce->roles()->sync(1, 3);
        $permissions_can_use = Permission::where('user_id', 0)
                                            ->get();
        foreach ($permissions_can_use as $permission) {
            $commerce->permissions()->attach($permission->id);
        }
    }

    function registerAdmin(Request $request) {
        User::create([
            'name' => ucwords($request->name),
            'status' => 'admin',
            'password' => bcrypt($request->password),
            'created_at' => Carbon::now(),
        ]);
    }

    function cobrar($admin_id) {
    	$collections = Collection::where('admin_id', $admin_id)
    							->where('delivered', 0)
    							->get();
    	foreach ($collections as $collection) {
    		$collection->delivered = 1;
    		$collection->save();
    	}
    }
}
