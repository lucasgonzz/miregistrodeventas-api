<?php

namespace App\Http\Controllers;

use App\Collection;
use App\Http\Controllers\Helpers\StringHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Recommendation;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;

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

    function user() {
        $auth_user = Auth()->user();
        if (is_null($auth_user->owner_id)) {
            $user = User::where('id', $auth_user->id)
                            ->with('plan.permissions')
                            ->with('plan.features')
                            ->with('addresses')
                            ->first();
        } else {
            $user = User::where('id', $auth_user->id)
                            ->with('permissions')
                            ->with('addresses')
                            ->first();
        }
        $user = UserHelper::checkUserTrial($user);
        return response()->json(['user' => $user], 200);
    }

    function store(Request $request) {
        $user = User::create([
            'name'              => $request->name,
            'city'              => 'Gualeguay',
            'email'             => $request->email,
            'company_name'      => $request->company_name,
            'status'            => 'commerce',
            'plan_id'           => 3,
            'type'              => 'commerce',
            // 'type'              => $request->type,
            'password'          => bcrypt($request->password),
            // 'iva'            => 'Responsable inscripto',
            'has_delivery'      => 1,
            'delivery_price'    => 0,
            'online_prices'     => 'all',
            'order_description' => '¿Hay que envolver algo?',
            'expired_at'         =>  Carbon::now()->addWeek(),
        ]);
    }

    // Confirguracion - Editar
    function update(Request $request) {
        $user = User::where('id', $this->userId())->with('employees')->first();
        $user->name = StringHelper::modelName($request->name, true);
        $user->has_delivery = $request->has_delivery;
        $user->delivery_price = $request->delivery_price;
        $user->online_prices = $request->online_prices;
        $user->dolar_plus = $request->dolar_plus;
        $user->order_description = $request->order_description;
        $user->save();
        $repeated_company_name = $this->isCompanyNameRepeated($request->company_name);
        if (!$repeated_company_name) {
            $user->company_name = ucwords($request->company_name);
            foreach ($user->employees as $employee) {
                $employee->company_name = ucwords($request->company_name);                
                $employee->save();
            }
            $user->save();
            return response()->json(['repeated' => false], 200);
        } else {
            return response()->json(['repeated' => true], 200);
        }
    }

    function isCompanyNameRepeated($company_name) {
        $user = User::where('company_name', $company_name)
                    ->where('id', '!=', $this->userId())
                    ->first();
        if (is_null($user)) {
            return false;
        }
        return true;
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

    function setPercentageCard(Request $request) {
        $user_id = Auth()->user()->id;
        $user = User::find($user_id);
        $user->percentage_card = $request->percentage_card;
        $user->save();
        foreach ($user->employees as $employee) {
            $employee->percentage_card = $request->percentage_card;
            $employee->save();
        }
    }

    public function updatePassword(Request $request) {

        if (Hash::check($request->current_password, Auth()->user()->password)) {
            $user = User::find(Auth()->user()->id);
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);
            return response()->json(['updated' => true], 200);
        } else {
            return response()->json(['updated' => false], 200);
        }
    }
}
