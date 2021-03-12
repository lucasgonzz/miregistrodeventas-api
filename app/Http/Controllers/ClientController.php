<?php

namespace App\Http\Controllers;

use App\Client;
use App\CurrentAcount;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    function index() {
    	$clients = Client::where('user_id', $this->userId())
                            ->where('status', 'active')
                            ->with('sales')
                            ->withCount('current_acounts')
                            ->get();
        return response()->json(['clients' => $this->setClientsSaldo($clients)], 200);
    }

    function update(Request $request) {
        $client = Client::find($request->client['id']);
        $client->name = ucwords($request->client['name']);
        $client->surname = ucwords($request->client['surname']);
        $client->address = ucwords($request->client['address']);
        $client->seller_id = $request->client['seller_id'] != 0 ? $request->client['seller_id'] : null;
        $client->save();
        return response()->json(['client' => $client], 200);
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
            'name' => ucwords($request->client['name']),
    		'surname' => ucwords($request->client['surname']),
            'user_id' => $this->userId(),
    		'seller_id' => $seller_id,
    	]);
        return response()->json(['client' => $client], 201);
    }

    function saldoInicial(Request $request) {
        $current_acount = CurrentAcount::create([
            'detalle' => 'Saldo inicial',
            'status'  => 'saldo_inicial',
            'client_id' => $request->client_id,
            'debe'    => $request->is_for_debe ? $request->saldo_inicial : null,
            'haber'   => !$request->is_for_debe ? $request->saldo_inicial : null,
            'saldo'   => $request->is_for_debe ? $request->saldo_inicial : -$request->saldo_inicial,
        ]);
        return response(null, 201);
    }

    function currentAcounts($client_id) {
        $current_acounts = CurrentAcount::where('client_id', $client_id)
                                        // ->where('status', '!=', 'pago_for_seller')
                                        ->orderBy('created_at', 'ASC')
                                        ->get();
        return response()->json(['current_acounts' => $current_acounts], 200);
    }

    function delete($id) {
    	$client = Client::find($id);
        $client->status = 'inactive';
        $client->save();
        return response(null, 200);
    }

    function setClientsDebt($clients) {
        foreach ($clients as $client) {
            $client->debt = 0;
            $debt = 0;
            foreach ($client->sales as $sale) {
                if (!is_null($sale->debt)) {
                    $total = 0;
                    foreach ($sale->articles as $article) {
                        $total += $article->pivot->price * $article->pivot->amount;
                    }
                    if (!is_null($sale->percentage_card)) {
                        $total = $total * floatval("1.".$sale->percentage_card);
                    }
                    $debt += $total - $sale->debt;
                }
            }
            $client->debt = $debt;
        }
        return $clients;
    }

    function setClientsSaldo($clients) {
        foreach ($clients as $client) {
            $last_current_acount = CurrentAcount::where('client_id', $client->id)
                                                ->orderBy('created_at', 'DESC')
                                                ->first();
            if (!is_null($last_current_acount)) {
                $client->saldo = $last_current_acount->saldo;
            } else {
                $client->saldo = '-';
            }
        }
        return $clients;
    }
}
