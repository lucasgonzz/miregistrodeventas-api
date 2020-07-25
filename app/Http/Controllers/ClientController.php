<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Client;

class ClientController extends Controller
{

    function index() {
    	$clients = Client::where('user_id', $this->userId())
                            ->with('sales')
                            ->get();
        return $this->setClientsDebt($clients); 
    }

    function update($id, Request $request) {
        $client = Client::find($id);
        $client->name = $request->client['name'];
        $client->save();
    }

    function search($client_name) {
        $clients = Client::where('user_id', $this->userId())
                        ->where('name', 'LIKE', "$client_name%")
                        ->get();
        return $this->setClientsDebt($clients);  
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
    	$client = Client::create([
    		'name' => ucwords($request->client['name']),
    		'user_id' => $this->userId()
    	]);
    	return $client;
    }

    function delete($id) {
    	Client::find($id)->delete();
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
                        // dd($total);
                    }
                    // dd($total);
                    $debt += $total - $sale->debt;
                }
                // if ($debt > 0) {
                //     dd($debt);
                // }
            }
            $client->debt = $debt;
        }
        return $clients;
    }
}
