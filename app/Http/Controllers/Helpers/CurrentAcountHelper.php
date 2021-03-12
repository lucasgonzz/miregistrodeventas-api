<?php

namespace App\Http\Controllers\Helpers;

use App\CurrentAcount;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Sale\SaleHelper;

class CurrentAcountHelper {

	static function getSaldo($client_id, $until_current_acount = null) {
        $query = CurrentAcount::where('client_id', $client_id)
                                ->orderBy('id', 'DESC');
        if (!is_null($until_current_acount)) {
            $last_current_acount = $query->where('id', '<', $until_current_acount->id)
                        ->first();
        } else {
            $last_current_acount = $query->first();
        }
        if (is_null($last_current_acount)) {
            return 0;
        } else {
            return $last_current_acount->saldo;
        }
    }

    static function getDescription($sale, $total = null) {
        if (count($sale->discounts) >= 1) {
            if (!is_null($total)) {
                $description = '$'.Numbers::price($total);
            } else {
                $description = '$'.Numbers::price(SaleHelper::getTotalSale($sale, false));
            }
            foreach ($sale->discounts as $discount) {
                $description .= '(-'.$discount->pivot->percentage . '% '. substr($discount->name, 0, 3) .')';
            }
            return $description;
        } else {
            return null;
        }
    }

}