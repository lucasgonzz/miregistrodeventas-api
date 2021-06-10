<?php

namespace App\Http\Controllers;

use App\Client;
use App\CurrentAcount;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintClients;
use App\Seller;
use Carbon\Carbon;
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

    function pdf($seller_id) {
        if ($seller_id == 'undefined') {
            $seller = new \stdClass();
            $seller->name = 'Oscar';
            $seller->surname = '';
            $clients = Client::where('seller_id', null)
                                ->get();
            $clients = $this->setClientsSaldo($clients);
        } else {
            $seller = Seller::where('id', $seller_id)
                            ->with('clients')
                            ->first();
            $clients = $this->setClientsSaldo($seller->clients);
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
            'status'  => 'sin_pagar',
            'client_id' => $request->client_id,
            'debe'    => $request->is_for_debe ? $request->saldo_inicial : null,
            'haber'   => !$request->is_for_debe ? $request->saldo_inicial : null,
            'saldo'   => $request->is_for_debe ? $request->saldo_inicial : -$request->saldo_inicial,
        ]);
        return response(null, 201);
    }

    function currentAcounts($client_id, $months_ago) {
        $current_acounts = CurrentAcountHelper::getCurrentAcountsSinceMonths($client_id, $months_ago);
        return response()->json(['current_acounts' => $current_acounts], 200);
    }


    function checkSaldos($client_id) {
        $current_acounts = CurrentAcount::where('client_id', $client_id)
                                        ->orderBy('id', 'ASC')
                                        ->get();
        foreach ($current_acounts as $current_acount) {
            // echo "detalle: ".$current_acount->detalle."</br>";
            if ($current_acount->debe) {
                // echo "debe: ".Numbers::price($current_acount->debe)."</br>";
                // echo "getSaldo = ".Numbers::price(CurrentAcountHelper::getSaldo($client_id, $current_acount))."</br>";
                $current_acount->saldo = Numbers::redondear(CurrentAcountHelper::getSaldo($client_id, $current_acount) + $current_acount->debe);
            }
            if ($current_acount->haber) {
                // echo "haber: ".Numbers::price($current_acount->haber)."</br>";
                $current_acount->saldo = Numbers::redondear(CurrentAcountHelper::getSaldo($client_id, $current_acount) - $current_acount->haber);
            }
            // echo "nuevo saldo: ".Numbers::price($current_acount->saldo)."</br>";
            // echo "----------------------------------------------------------------</br>";
            $current_acount->save();
        }
        $current_acount_controller = new CurrentAcountController();
        $current_acount_controller->checkPagos($client_id);
        return response(null, 200);
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
                // $client->saldo = '-';
            }
        }
        return $clients;
    }
}
