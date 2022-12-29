<?php

namespace App\Http\Controllers;

use App\Client;
use App\CurrentAcount;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ClientHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintClients;
use App\Http\Controllers\Helpers\Pdf\ClientPdf;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SearchController;
use App\Imports\ClientsImport;
use App\Sale;
use App\Seller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{

    function index() {
    	$clients = Client::where('user_id', $this->userId())
                            ->withAll()
                            ->orderBy('id', 'DESC')
                            ->where('status', 'active')
                            ->get();
        return response()->json(['models' => $clients], 200);
    }

    function show($id) {
        return response()->json(['model' => $this->fullModel('App\Client', $id)], 200);
    }

    function store(Request $request) {
        $seller_id = $request->seller_id != 0 ? $request->seller_id : null;
        $client = Client::create([
            'name'              => $request->name,
            'phone'             => $request->phone,
            'email'             => $request->email,
            'address'           => $request->address,
            'location_id'       => $request->location_id,
            'cuit'              => $request->cuit,
            'description'       => $request->description,
            'razon_social'      => $request->razon_social,
            'iva_condition_id'  => $request->iva_condition_id,
            'price_type_id'     => $request->price_type_id,
            'saldo'             => 0,
            'seller_id'         => $seller_id,
            'user_id'           => $this->userId(),
        ]);
        $client = ClientHelper::getFullModel($client->id);
        return response()->json(['model' => $client], 201);
    }

    function update(Request $request, $id) {
        $client = Client::find($id);
        $client->name               = $request->name;
        $client->phone              = $request->phone;
        $client->email              = $request->email;
        $client->address            = $request->address;
        $client->location_id        = $request->location_id;
        $client->cuit               = $request->cuit;
        $client->description        = $request->description;
        $client->razon_social       = $request->razon_social;
        $client->iva_condition_id   = $request->iva_condition_id;
        $client->price_type_id      = $request->price_type_id;
        $client->seller_id          = $request->seller_id;
        $client->save();
        $client = ClientHelper::getFullModel($client->id);
        return response()->json(['model' => $client], 200);
    }

    function setComercioCityUser(Request $request) {
        $user = User::where('company_name', $request->company_name)
                        ->first();
        if (!is_null($user)) {
            $client = Client::find($request->model_id);
            $client->comercio_city_user_id = $user->id;
            $client->save();
            return response()->json(['user_finded' => true, 'model' => $this->fullModel('App\Client', $client->id), 200]);
        }
        return response()->json(['user_finded' => false, 200]);
    }

    function pdf(Request $request) {
        // dd(!is_null($request->get('fitlers')));
        if (isset($request->all()['filters'])) {
            // dd($request->all()['filters']);
            $_filters = json_decode($request->all()['filters']);
            $ct = new SearchController();
            $filters = [];
            foreach ($_filters as $filter) {
                $filters[] = (array)$filter;
            }
            // dd($filters);
            $models = $ct->search($request, 'client', $filters);
            // dd($models);
        } else {
            $models = Client::where('user_id', $this->userId())
                            ->get();
        }
            // $models = Client::where('user_id', $this->userId())
                            // ->get();
        $pdf = new ClientPdf($models);
    }

    function export() {
        return Excel::download(new ClientExport, 'comerciocity-clientes.xlsx');
    }

    function isRegister($client_name) {
        $client = Client::where('name', ucwords($client_name))
                            ->first();
        if ($client === null) {
            return ['repetido' => false];
        } else {
            return ['repetido' => true];
        }
    }

    function saldoInicial(Request $request) {
        $current_acount = CurrentAcount::create([
            'detalle'   => 'Saldo inicial',
            'status'    => $request->is_for_debe ? 'sin_pagar' : 'pago_from_client',
            'client_id' => $request->client_id,
            'debe'      => $request->is_for_debe ? $request->saldo_inicial : null,
            'haber'     => !$request->is_for_debe ? $request->saldo_inicial : null,
            'saldo'     => $request->is_for_debe ? $request->saldo_inicial : -$request->saldo_inicial,
        ]);
        return response()->json(['current_acount' => $current_acount], 201);
    }

    // function currentAcounts($client_id, $months_ago) {
    //     // $this->checkCurrentAcounts($client_id);
    //     CurrentAcountHelper::checkSaldos($client_id);
    //     $current_acounts = CurrentAcountHelper::getCurrentAcountsSinceMonths($client_id, $months_ago);
    //     return response()->json(['current_acounts' => $current_acounts], 200);
    // }

    function destroy($id) {
    	$client = Client::find($id);
        $client->status = 'inactive';
        $client->save();
        return response(null, 200);
    }

    function setClientsSaldo($clients) {
        foreach ($clients as $client) {
            $last_current_acount = CurrentAcount::where('client_id', $client->id)
                                                ->orderBy('created_at', 'DESC')
                                                ->first();
            if (!is_null($last_current_acount)) {
                $client->saldo = $last_current_acount->saldo;
            } else {
                // $client->saldo = '-';
            }
        }
        return $clients;
    }

    function import(Request $request) {
        Excel::import(new ClientsImport, $request->file('clients'));
    }
}
