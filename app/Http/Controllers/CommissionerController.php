<?php

namespace App\Http\Controllers;

use App\Commission;
use App\Commissioner;
use App\Http\Controllers\Helpers\CommissionHelper;
use App\Http\Controllers\Helpers\Numbers;
use Illuminate\Http\Request;

class CommissionerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $commissioners = Commissioner::where('user_id', $this->userId())
                                    ->with('seller')
                                    ->get();
        return response()->json(['commissioners' => $commissioners], 200);
    }


    function checkSaldos($commissioner_id) {
        $commissioner = Commissioner::find($commissioner_id);
        $commissions = Commission::where('commissioner_id', $commissioner_id)
                                        ->orderBy('created_at', 'ASC')
                                        ->get();
        foreach ($commissions as $commission) {
            $commission->saldo = Numbers::redondear(CommissionHelper::getCommissionerSaldo($commissioner, $commission) + $commission->monto);
            $commission->save();
        }
        return response(null, 200);
    }
}
