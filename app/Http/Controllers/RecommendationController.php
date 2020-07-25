<?php

namespace App\Http\Controllers;

use App\Recommendation;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Caffeinated\Shinobi\Models\Permission;

class RecommendationController extends Controller
{
    function isConfirmed($company_name, $index) {
        $commerce = User::where('company_name', str_replace('-', ' ', $company_name))
                        ->first();
        $recommendations = Recommendation::where('commerce_id', $commerce->id)
                                            ->get();
        $cant_recommendations = count($recommendations) >= 1 ? count($recommendations) : 0;
        if ($cant_recommendations == $index) {
            return [
                'confirmed' => true,
            ];
        } else {
            return [
                'confirmed' => false,
            ];
        }
    }
    
    function confirm(Request $request) {
        $recommendations = Recommendation::where('commerce_id', $request->commerce_id)
                                        ->get();
        $cant_recommendations = count($recommendations) >= 1 ? count($recommendations) : 0;
        $commerce = User::find($request->commerce_id);
        $expire = new Carbon($commerce->expire);
        if ($cant_recommendations == 4) {
        	// Esta es la quinta invitacion
            $commerce->expire = $expire->addYear();
        } else {
            $commerce->expire = $expire->addMonth();
        }
        $commerce->save();
        Recommendation::create([
            'commerce_id' => $request->commerce_id,
            'admin_id' => $request->admin_id,
            'name' => ucwords($request->name),
            'address' => ucwords($request->address),
        ]);

        $user_trial = User::create([
            'name' => ucwords($request->name),
            'city' => 'Gualeguay',
            'online' => 1,
            'private' => 0,
            'address' => '25 de Mayo 3412',
            'company_name' => ucwords($request->name),
            'status' => 'without_use',
            'password' => bcrypt('1234'),
            'admin_id' => $request->admin_id,
            'created_at' => Carbon::now(),
            'expire' => Carbon::now()->addWeeks(2),
        ]);
        // 1 es el rol de owner, 3 el de comercio
        $user_trial->roles()->sync(1, 3);
        $permissions_can_use = Permission::where('user_id', 0)
                                            ->get();
        foreach ($permissions_can_use as $permission) {
            $user_trial->permissions()->attach($permission->id);
        }
    }
}
