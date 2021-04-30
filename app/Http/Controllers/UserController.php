<?php

namespace App\Http\Controllers;

use App\Collection;
use App\Http\Controllers\Helpers\StringHelper;
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

    function collections() {
        return Collection::where('commerce_id', Auth()->user()->id)
                        ->get();
    }

    // Confirguracion - Editar
    function update(Request $request) {
        $user = User::where('id', $this->userId())->with('employees')->first();
        $user->name = StringHelper::modelName($request->name, true);
        $user->deliver_amount = StringHelper::modelName($request->deliver_amount, true);
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
            return response()->json(['updated' => true], 200);
        } else {
            return response()->json(['updated' => false], 200);
        }
    }
}
