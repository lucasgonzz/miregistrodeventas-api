<?php

namespace App\Http\Controllers;

use App\Client;
use App\CurrentAcount;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ClientHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintClients;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\SaleController;
use App\Imports\ClientsImport;
use App\Sale;
use App\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{

    function index() {
    	$clients = Client::where('user_id', $this->userId())
                            ->withAll()
                            ->orderBy('id', 'DESC')
                            ->get();
        return response()->json(['models' => ClientHelper::setClientsSaldo($clients)], 200);
    }

    function show($id) {
        $client = ClientHelper::getFullModel($id);
        return response()->json(['model' => $client], 200);
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
            'razon_social'      => $request->razon_social,
            'iva_condition_id'  => $request->iva_condition_id,
            'price_type_id'     => $request->price_type_id,
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
        $client->razon_social       = $request->razon_social;
        $client->iva_condition_id   = $request->iva_condition_id;
        $client->price_type_id      = $request->price_type_id;
        $client->seller_id          = $request->seller_id;
        $client->save();
        $client = ClientHelper::getFullModel($client->id);
        return response()->json(['model' => $client], 200);
    }

    function pdf($seller_id) {
        if ($seller_id == 'undefined') {
            $seller = new \stdClass();
            $seller->name = 'Oscar';
            $seller->surname = '';
            $clients = Client::where('seller_id', null)
                                ->get();
            $clients = ClientHelper::setClientsSaldo($clients);
        } else {
            $seller = Seller::where('id', $seller_id)
                            ->with('clients')
                            ->first();
            $clients = ClientHelper::setClientsSaldo($seller->clients);
        }
        // dd($seller->clients);
        $pdf = new PdfPrintClients($seller, $clients);
        $pdf->printClients();
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
