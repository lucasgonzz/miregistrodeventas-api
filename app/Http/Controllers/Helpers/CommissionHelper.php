<?php

namespace App\Http\Controllers\Helpers;

use App\Commission;

class CommissionHelper {

    static function getCommissionerSaldo($commissioner, $until_commission = null) {
        $query = Commission::where('commissioner_id', $commissioner->id)
                    ->where('status', 'active')
                    ->orderBy('id', 'DESC');
        if (!is_null($until_commission)) {
            $last_commission = $query->where('id', '<', $until_commission->id)
                        ->first();
        } else {
            $last_commission = $query->first();
        }
        if (!is_null($last_commission)) {
            return $last_commission->saldo;
        } else {
            return 0;
        }
    }

}