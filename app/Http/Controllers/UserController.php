<?php

namespace App\Http\Controllers;

use App\AfipInformation;
use App\Collection;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\StringHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Recommendation;
use App\User;
use App\UserConfiguration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Validator;

class UserController extends Controller
{

    function user() {
        $user = UserHelper::getFullModel($this->userId(false));
        $user = UserHelper::checkUserTrial($user);
        if ($user->owner_id) {
            $user = UserHelper::setEmployeeExtencionsAndConfigurations($user);
        }
        return response()->json(['user' => $user], 200);
    }

    function store(Request $request) {
        $user = User::create([
            'name'              => $request->name,
            'city'              => 'Gualeguay',
            'email'             => $request->email,
            'company_name'      => $request->company_name,
            'status'            => 'commerce',
            'plan_id'           => 6,
            // 'type'              => 'commerce',
            'type'              => $request->type,
            'password'          => bcrypt($request->password),
            // 'iva'            => 'Responsable inscripto',
            'has_delivery'      => 1,
            'delivery_price'    => 0,
            'online_prices'     => 'all',
            'order_description' => '¿Hay que envolver algo?',
            'expired_at'         =>  Carbon::now()->addMonth(),
            'created_at'         =>  Carbon::now(),
        ]);
        $commerce->extencions()->attach([1, 3, 4, 7, 8]);

        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Recibo de pago (saldado)',
            'current_acount_pagandose_details'      => 'Recibo de pago',
            'iva_included'                          => 1,
            'user_id'                               => $user->id,
        ]);
        AfipInformation::create([
            'iva_condition_id'      => 1,
            'razon_social'          => strtoupper($request->company_name),
            'domicilio_comercial'   => '',
            'cuit'                  => '',
            'punto_venta'           => null,
            'ingresos_brutos'       => '',
            'inicio_actividades'    => null,
            'user_id'               => $user->id,
        ]);

    }

    // Confirguracion - Editar
    function update(Request $request) {
        $user = User::where('id', $this->userId())->with('employees')->first();
        $user->name                             = StringHelper::modelName($request->name, true);
        $user->phone                            = $request->phone;
        $user->online_description               = $request->online_description;
        $user->has_delivery                     = $request->has_delivery;
        $user->delivery_price                   = $request->delivery_price;
        $user->online_prices                    = $request->online_prices;
        $user->dollar                           = $request->dollar;
        $user->order_description                = $request->order_description;
        $user->show_articles_without_images     = $request->show_articles_without_images;
        $user->save();


        $configuration = UserConfiguration::where('user_id', $this->userId())
                                            ->first();
        $current_value = $configuration->iva_included;
        $configuration->show_articles_without_stock     = $request->configuration['show_articles_without_stock'];
        $configuration->iva_included                    = $request->configuration['iva_included'];
        $configuration->set_articles_updated_at_always  = $request->configuration['set_articles_updated_at_always'];
        $configuration->limit_items_in_sale_per_page    = $request->configuration['limit_items_in_sale_per_page'];
        $configuration->apply_price_type_in_services    = $request->configuration['apply_price_type_in_services'];
        $configuration->save();
        GeneralHelper::checkNewValuesForArticlesPrices($current_value, $request->configuration['iva_included']);

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

    function updateImage(Request $request, $id) {
        $user = User::find($id);
        $user->image_url = ImageHelper::saveHostingImage($request->image_url);
        $user->save();
        return response()->json(['user' => UserHelper::getFullModel($id)], 200);
    } 

    function defautlArticleImage(Request $request) {
        $user = User::find($this->userId());
        $user->default_article_image_url = ImageHelper::saveHostingImage($request->image_url);
        $user->save();
        return response()->json(['user' => UserHelper::getFullModel()], 200);
    }

    function isCompanyNameRepeated($company_name) {
        $user = User::where('company_name', $company_name)
                    ->where('id', '!=', $this->userId())
                    ->whereNull('owner_id')
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
