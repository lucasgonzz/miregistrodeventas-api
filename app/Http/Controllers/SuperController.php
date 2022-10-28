<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Permission;
use App\Plan;
use App\Collection;
use Carbon\Carbon;

class SuperController extends Controller
{
    // function admins() {
    //     return User::where('status', 'admin')
    //                 ->with('commerces')
    //                 ->get();
    // }

    function plans() {
        $plans = Plan::with('permissions')
                        ->get();
        return response()->json(['plans' => $plans], 200);
    }

    function permissions() {
        $permissions = Permission::all();
        return response()->json(['permissions' => $permissions], 200);
    }

    function updateCommerce(Request $request) {
        $commerce = User::find($request->id);
        $commerce->name = $request->name;
        $commerce->company_name = $request->company_name;
        $commerce->phone = $request->phone;
        $commerce->email = $request->email;
        $commerce->plan_id = $request->plan_id;
        $commerce->save();
        $commerce = User::where('id', $commerce->id)
                        ->with('plan.permissions')
                        ->with('addresses')
                        ->first();
        return response()->json(['commerce' => $commerce], 200);
    }

    function updatePlan(Request $request) {
        $plan = Plan::find($request->id);
        $permissions = [];
        $plan->permissions()->sync($request->permissions_id);
        $plan = Plan::where('id', $plan->id)
                        ->with('permissions')
                        ->first();
        return response()->json(['plan' => $plan], 200);
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
