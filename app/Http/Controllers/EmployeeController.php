<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    

	function index() {
		$employees = User::where('owner_id', $this->userId())
                    ->with('permissions')
                    ->get();
		return response()->json(['employees' => $employees], 200);
	}      

    function update(Request $request) {
        $employee = User::where('id', $request->id)
                        ->first();

        $employee->syncPermissions($request->permissions_id);

        $employee = User::where('id', $request->id)
        				->with('permissions')
                        ->first();
        return response()->json(['employee' => $employee], 200);
    }

	function delete($id) {
		$user = User::find($id);
        $user->delete();
	}

    function store(Request $request) {
    	$user = auth()->user();
        $employee = User::where('owner_id', $this->userId())
                            ->where('name', $request->name)
                            ->first();


        if (is_null($employee)) {
        	$employee = User::create([
                'name' => ucwords($request->name),
        		'company_name' => $user->company_name,
        		'password' => Hash::make($request->password),
                'owner_id' => $user->id,
                'percentage_card' => $user->percentage_card,
        		'online' => $user->online,
                'created_at' => Carbon::now(),
        	]);

        	if ($user->hasRole('provider')) {
                $employee->syncRoles('provider');
        	} else {
                $employee->syncRoles('commerce');
        	}
        	$employee->permissions()->attach($request->permissions_id);
            $employee = User::where('id', $employee->id)
                                ->with('permissions')
                                ->first();
        	return response()->json(['repeated' => false, 'employee' => $employee], 201);
        } else {
        	return response()->json(['repeated' => true], 200);
        }
    }
}
