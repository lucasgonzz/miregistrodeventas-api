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
use App\Http\Controllers\Helpers\Sale\SaleHelper;
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
                            ->where('status', 'active')
                            ->with('sales')
                            ->with('iva_condition')
                            ->withCount('current_acounts')
                            ->orderBy('id', 'DESC')
                            ->get();
        return response()->json(['clients' => ClientHelper::setClientsSaldo($clients)], 200);
    }

    function show($id) {
        $client = ClientHelper::getFullModel($id);
        return response()->json(['client' => $client], 200);
    }

    function update(Request $request) {
        $client = Client::find($request->client['id']);
        $client->name = ucwords($request->client['name']);
        $client->surname = ucwords($request->client['surname']);
        $client->address = ucwords($request->client['address']);
        $client->cuit = $request->client['cuit'];
        $client->razon_social = $request->client['razon_social'];
        $client->seller_id = $request->client['seller_id'] != 0 ? $request->client['seller_id'] : null;
        $client->iva_condition_id = $request->client['iva_condition_id'] != 0 ? $request->client['iva_condition_id'] : null;
        $client->save();
        return response()->json(['client' => $client], 200);
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

    function store(Request $request) {
        $seller_id = $request->client['seller_id'] == 0 ? null : $request->client['seller_id'];
    	$client = Client::create([
            'name'              => ucwords($request->client['name']),
            'surname'           => ucwords($request->client['surname']),
            'address'           => ucwords($request->client['address']),
            'cuit'              => $request->client['cuit'],
            'razon_social'      => $request->client['razon_social'],
    		'iva_condition_id'  => $request->client['iva_condition_id'],
            'user_id'           => $this->userId(),
    		'seller_id'         => $seller_id,
    	]);
        return response()->json(['client' => $client], 201);
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

    function currentAcounts($client_id, $months_ago) {
        // $this->checkCurrentAcounts($client_id);
        CurrentAcountHelper::checkSaldos($client_id);
        $current_acounts = CurrentAcountHelper::getCurrentAcountsSinceMonths($client_id, $months_ago);
        return response()->json(['current_acounts' => $current_acounts], 200);
    }

    function delete($id) {
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
