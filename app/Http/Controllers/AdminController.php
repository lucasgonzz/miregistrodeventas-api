<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Recommendation;
use App\Collection;
use App\Http\Controllers\Helpers\AdminHelper;
use Carbon\Carbon;

class AdminController extends Controller
{

    // Recomendations
    function recommendations() {
        return Recommendation::where('admin_id', Auth()->user()->id)
                                ->where('done', 0)
                                ->with('commerce')
                                ->get();
    }

    function confirmRecommendation($recommendation_id) {
        $recommendation = Recommendation::find($recommendation_id);
        $recommendation->done = 1;
        $recommendation->save();
    }

    // usuarios sin uso
    function getUsersForTrial() {
        $user = Auth()->user();
        return User::where('admin_id', $user->id)
                    ->where('status', 'for_trial')
                    ->get();
    }

    function userStartTrial($user_id) {
        $user = Auth()->user();
        $user = User::find($user_id);
        $user->status = 'trial';
        $today = Carbon::now();
        $user->expire = $today->addWeeks(2);
        $user->save();
    }

    // usuarios de prueba
    function getUsersTrial() {
        $user = Auth()->user();
        return User::where('admin_id', $user->id)
                    ->where('status', 'trial')
                    ->get();
    }

    // usuarios en uso
    function getUsersInUse() {
        $users = User::where('admin_id', Auth()->user()->id)
                    ->where('status', 'in_use')
                    ->whereNull('owner_id')
                    ->with('permissions')
                    ->get();

        // Se setea la fecha en la que se tiene que ir a cobrar
        // return AdminHelper::setFinishDate($users);
        return $users;
    }

    function updateUserPermissions($user_id, Request $request) {
        $user = User::find($user_id);
        $user->permissions()->sync($request->permissions);
        foreach ($user->employees as $employee) {
            $permissions_employee = [];
            foreach ($employee->permissions as $permission) {
                if (is_null($permission->user_id)) {
                    $permissions_employee[] = $permission->id;
                }
            }
            $employee->permissions()->sync($permissions_employee);
            $employee->permissions()->attach($request->permissions);
        }
    }

    function getCollections() {
        return Collection::where('admin_id', Auth()->user()->id)
                            ->with('commerce')
                            ->orderBy('id', 'DESC')
                            ->get();
    }

    function getCollectionsWithoutDelivered() {
        return Collection::where('admin_id', Auth()->user()->id)
                            ->where('delivered', 0)
                            ->get();
    }

    // Cobrar el mes a un comercio
    function cobrar($commerce_id, $collected_months, $collected_per_month) {
        $commerce = User::find($commerce_id);
        $expire = new Carbon($commerce->expire);
        $commerce->expire = $expire->addMonths($collected_months);
        $commerce->save();

        // Se crea el pago
        $collections = Collection::where('commerce_id', $commerce_id)
                                ->first();

        $delivered = false;
        // Si es null es que es la primera vez que le combra, entoces la plata es toda para el admin
        if ($collections === null) {
            $delivered = true;
        }
        Collection::create([
            'admin_id' => Auth()->user()->id,
            'commerce_id' => $commerce_id,
            'collected_months' => $collected_months,
            'collected_per_month' => $collected_per_month,
            'delivered' => $delivered,
        ]);
    }

    function collect($commerce_id, $collected_months) {
        $user = User::find($commerce_id);

        $expire = new Carbon($user->expire);
        $user->expire = $expire->addMonths($collected_months);
        $user->save();
    }
}
