<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\UserHelper;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    

	function index() {
		$models = User::where('owner_id', $this->userId())
                    ->with('permissions')
                    ->orderBy('created_at', 'DESC')
                    ->get();
		return response()->json(['models' => $models], 200);
	}      

    function update(Request $request, $id) {
        $model = User::where('id', $request->id)
                        ->first();

        $model->permissions()->sync($request->permissions_id);
        $model->visible_password    = $request->visible_password;
        $model->password            = bcrypt($request->visible_password);
        $model->dni                 = $request->dni;
        $model->save();

        $model = User::where('id', $request->id)
        				->with('permissions')
                        ->first();
        return response()->json(['model' => $model], 200);
    }

	function destroy($id) {
		$user = User::find($id);
        $user->delete();
	}

    function store(Request $request) {
    	$user = auth()->user();
        $model = User::where('dni', $request->dni)
                        ->first();


        if (is_null($model)) {
        	$model = User::create([
                'name'              => ucfirst($request->name),
                'dni'               => $request->dni,
        		'company_name'      => $user->company_name,
                'visible_password'  => $request->visible_password,
        		'password'          => Hash::make($request->visible_password),
                'owner_id'          => UserHelper::userId(),
                'percentage_card'   => $user->percentage_card,
                'type'              => $user->type,
        		'status'            => 'commerce',
                'created_at'        => Carbon::now(),
                'expired_at'        => $user->expired_at,
        	]);

        	$model->permissions()->attach($request->permissions_id);
            $model = User::where('id', $model->id)
                                ->with('permissions')
                                ->first();
        	return response()->json(['repeated' => false, 'model' => $model], 201);
        } else {
        	return response()->json(['repeated' => true], 200);
        }
    }
}
