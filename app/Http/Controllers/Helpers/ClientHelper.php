<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Client;
use App\CurrentAcount;
use Carbon\Carbon;

class ClientHelper {

	static function getFullModel($id) {
        $client = Client::where('id', $id)
	                        ->with('sales')
	                        ->with('iva_condition')
	                        ->withCount('current_acounts')
	                        ->first();
	    $client = Self::setClientsSaldo([$client])[0];
	    return $client;
	}

    static function setClientsSaldo($clients) {
        foreach ($clients as $client) {
            $last_current_acount = CurrentAcount::where('client_id', $client->id)
                                                ->orderBy('created_at', 'DESC')
                                                ->first();
            if (!is_null($last_current_acount)) {
                $client->saldo = $last_current_acount->saldo;
            } 
        }
        return $clients;
    }

}