<?php

namespace App\Http\Controllers;

use App\Commission;
use App\Commissioner;
use App\Http\Controllers\Helpers\CommissionHelper;
use App\Sale;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    function store($sale) {
    	Commission::create([
    		'sale_id' => $sale->id,
    	]);
    }

    function fromCommissioner($commissioner_id) {
        $commissions = Commission::where('commissioner_id', $commissioner_id)
                                    ->where('status', 'active')
                                    ->orderBy('id', 'ASC')
                                    ->get();
        return response()->json(['commissions' => $commissions], 200);
    }

    function updateToActive($current_acount) {
        $commissions = Commission::where('sale_id', $current_acount->sale_id)
                                    ->where('page', $current_acount->page)
                                    ->get();
        foreach ($commissions as $commission) {
            $commissioner = Commissioner::find($commission->commissioner_id);
            $commission->status = 'active';
            // $commission->saldo = CommissionHelper::getCommissionerSaldo($commissioner) + ;
            $commission->saldo = CommissionHelper::getCommissionerSaldo($commissioner) + $commission->monto;
            $commission->save();
        }
    }

    function updatePercentage(Request $request) {
        $commission = Commission::find($request->id);
        $commission->percentage = $request->percentage;
    }

    public function pagoForCommissioner(Request $request) {
        $commissioner = Commissioner::find($request->commissioner_id);
        $commission = Commission::create([
            'commissioner_id' => $commissioner->id,
            'detalle'         => 'Dejo de pago',
            'saldo'           => CommissionHelper::getCommissionerSaldo($commissioner) - $request->pago,
            'monto'           => $request->pago,
        ]);
        return response(null, 201);
    }

    function delete($sale) {
        $commissions_to_delete = Commission::where('sale_id', $sale->id)
                                        ->get();
        $commissioners_id = [];
        $ultimas_a_eliminar = [];
        foreach ($commissions_to_delete as $index => $commission_to_delete) {
            if ($this->cambioElCommissioner($commission_to_delete, $commissioners_id)) {
                $commissioners_id[] = $commission_to_delete->commissioner_id;
                if ($index != 0) {
                    $ultimas_a_eliminar[] = $commissions_to_delete[$index-1];
                }
            }
            if ($index == count($commissions_to_delete)-1) {
                $ultimas_a_eliminar[] = $commissions_to_delete[$index];
            }
        }
        $commissions_que_siguen_of_all_commissioners = [];
        foreach ($ultimas_a_eliminar as $index => $ultima_a_eliminar) {
            $commissions_que_siguen_of_all_commissioners[] = Commission::where('commissioner_id', $commissioners_id[$index])
                                                    ->where('id', '>', $ultima_a_eliminar->id)
                                                    ->orderBy('created_at', 'ASC')
                                                    ->get();
        }
        foreach ($commissions_to_delete as $commission_to_delete) {
            $commission_to_delete->delete();
        }
        $this->updateSaldo($sale, $commissions_que_siguen_of_all_commissioners);
    }

    function updateSaldo($sale, $commissions_que_siguen_of_all_commissioners) {
        foreach ($commissions_que_siguen_of_all_commissioners as $commissions_que_siguen_of_commissioner) {
            foreach ($commissions_que_siguen_of_commissioner as $commission) {
                $commission->saldo = CommissionHelper::getCommissionerSaldo($commission->commissioner, $commission) + $commission->monto;
                $commission->timestamps = false;
                $commission->save();
            }
        }
    }

    function cambioElCommissioner($commission_to_delete, $commissioners_id) {
        return count($commissioners_id) < 1 || $commissioners_id[count($commissioners_id)-1] != $commission_to_delete->commissioner_id;
    }
}
