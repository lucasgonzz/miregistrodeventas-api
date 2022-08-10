<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Check;
use App\CurrentAcount;
use App\ErrorCurrentAcount;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CurrentAcountHelper {

    static function pagadoDetails() {
        $user = UserHelper::getFullModel();
        return $user->configuration->current_acount_pagado_details;
    }

    static function pagandoseDetails() {
        $user = UserHelper::getFullModel();
        return $user->configuration->current_acount_pagandose_details;
    }

    static function getNumReceipt() {
        $last_receipt = CurrentAcount::where('user_id', UserHelper::userId())
                                        ->where('status', 'pago_from_client')
                                        ->where('status', '!=', 'nota_credito')
                                        ->orderBy('created_at', 'DESC')
                                        ->first();
        return is_null($last_receipt) ? 1 : $last_receipt->num_receipt + 1;
    }

    static function getSaldo($client_id, $until_current_acount) {
        Log::info('Buscando ultimo saldo del cliente '.$until_current_acount->client->name.' hasta la cuenta corriente: '.$until_current_acount->detalle.'. Creada en: '.date_format($until_current_acount->created_at, 'd/m/Y H:i:s'));
        $last_current_acount = CurrentAcount::where('client_id', $client_id)
                                ->orderBy('created_at', 'DESC')
                                ->where('created_at', '<', $until_current_acount->created_at)
                                ->first();
        if (is_null($last_current_acount)) {
            return 0;
        } else {
            Log::info('Retornando saldo: '.$last_current_acount->saldo.' de la cuenta corriente '.$last_current_acount->detalle.'. Creada en: '.date_format($last_current_acount->created_at, 'd/m/Y H:i:s'));
            return $last_current_acount->saldo;
        }
    }

    static function restartCurrentAcounts($client_id) {
        CurrentAcount::where('client_id', $client_id)
                    ->whereNotNull('debe')
                    ->update([
                        'status'    => 'sin_pagar',
                        'pagandose' => null,
                    ]);
        Self::checkSaldoInicial($client_id);
        Self::checkSaldos($client_id);
    }

    static function createCurrentAcountsFromSales($client_id, $from_sale) {
        $sales = Sale::where('client_id', $client_id)
                        ->where('created_at', '>=', $from_sale['created_at'])
                        ->where('user_id', UserHelper::userId())
                        ->get();
        foreach ($sales as $sale) {
            if (count($sale->articles) >= 1) {
                SaleHelper::updateCurrentAcountsAndCommissions($sale);
            }
        }
    }

    static function createCurrentAcount($sale, $current_acount) {
        $current_acount = CurrentAcount::create([
            'detalle'     => $current_acount['detalle'],
            'page'        => 1,
            'debe'        => $current_acount['debe'],
            'status'      => 'sin_pagar',
            'client_id'   => $sale->client_id,
            'seller_id'   => $sale->client->seller_id,
            'sale_id'     => $sale->id,
            'created_at'  => $sale->created_at,
        ]);
        Log::info('Se creo remito para venta sin articulos: '.$current_acount->detalle);
    }

    static function pagoFromClient($data) {
        $pago = CurrentAcount::create([
            'haber'                             => $data['haber'],
            'status'                            => 'pago_from_client',
            'user_id'                           => UserHelper::userId(),
            'num_receipt'                       => Self::getNumReceipt(),
            'client_id'                         => $data['client_id'],
            'current_acount_payment_method_id'  => $data['current_acount_payment_method_id'],
            'created_at'                        => $data['current_date'] ? Carbon::now() : $data['created_at'],
        ]);
        $to_pay_id = !is_null($data['to_pay']) ? $data['to_pay']['id'] : null;
        $pago->saldo = Self::getSaldo($pago->client_id, $pago) - $data['haber'];
        $pago->detalle = Self::procesarPago($data['haber'], $data['client_id'], $pago, $to_pay_id);
        $pago->save();
        Self::saveCheck($pago, $data['checks']);
        return $pago;
    }

    static function saveCheck($pago, $checks) {
        foreach ($checks as $check) {
            Check::create([
                'bank'                  => $check['bank'],
                'payment_date'          => $check['payment_date'],
                'amount'                => $check['amount'],
                'num'                   => $check['num'],
                'current_acount_id'     => $pago->id,
            ]);
        }
    }

    static function notaCredito($haber, $description, $client_id) {
        $nota_credito = CurrentAcount::create([
            // 'detalle'       => 'N.C. '.$detalle,
            'description'   => $description,
            'haber'         => $haber,
            'status'        => 'nota_credito',
            'client_id'     => $client_id,
        ]);
        $nota_credito->saldo = Self::getSaldo($nota_credito->client_id, $nota_credito) - $haber;
        $nota_credito->detalle = 'N.C '.Self::procesarPago($nota_credito->haber, $nota_credito->client_id, $nota_credito);
        $nota_credito->save();
        return $nota_credito;
    }

    static function procesarPago($haber, $client_id, $until_pago = null, $to_pay_id = null) {
        if (!is_null($to_pay_id)) {
            $until_pago->to_pay_id = $to_pay_id;
            $until_pago->save();
            $detalle = Self::saldarSpecificCurrentAcount($to_pay_id, $haber);
        } else {
            $saldar_pagandose = Self::saldarPagandose($haber, $client_id, $until_pago);
            $haber_restante = $saldar_pagandose['haber'];
            $detalle = $saldar_pagandose['detalle'];    
            Log::info('saldarPagandose detalle: '.$detalle);
            $detalle .= Self::saldarCuentasSinPagar($haber_restante, $client_id, $until_pago);
            Log::info('saldarCuentasSinPagar detalle: '.$detalle);
        }
        return $detalle;
    }

    static function saldarPagandose($haber, $client_id, $until_pago) {
        $detalle = '';
        $sin_pagar = Self::getFirstSinPagar($client_id, $until_pago);
        $pagandose = Self::getFirstPagandose($client_id, $until_pago);
        while (!is_null($sin_pagar) && !is_null($pagandose) && $sin_pagar->created_at->lt($pagandose->created_at) && $haber > 0) {
            $res = Self::saldarCurrentAcount($sin_pagar, $haber);
            $detalle .= $res['detalle'];
            $haber = $res['haber'];
            $sin_pagar = Self::getFirstSinPagar($client_id, $until_pago);
            $pagandose = Self::getFirstPagandose($client_id, $until_pago);
            Log::info('Cuenta sin pagar despues de haber saldado la que estaba:');
            Log::info($sin_pagar);
        }
        while (!is_null($pagandose) && $haber > 0) {
            Log::info('Cuenta pagandose:');
            Log::info($pagandose->detalle);
            Log::info('Hay disponibles '.$haber.' y tiene '.$pagandose->pagandose.' a favor');
            
            $haber += $pagandose->pagandose;
            $pagandose->pagandose = 0;
            $pagandose->save();
            
            Log::info('Ahora hay '.$haber);
            
            $res = Self::saldarCurrentAcount($pagandose, $haber);
            $detalle .= $res['detalle'];
            $haber = $res['haber'];
            
            Log::info('Ahora hay '.$haber);
            
            $pagandose = Self::getFirstPagandose($client_id, $until_pago);
        }
        return [
            'haber'   => $haber,
            'detalle' => $detalle,
        ];
    }

    static function saldarCuentasSinPagar($haber, $client_id, $until_pago = null) {
        $detalle = '';
        $sin_pagar = Self::getFirstSinPagar($client_id, $until_pago);
        while (!is_null($sin_pagar) && $haber > 0) {
            $res = Self::saldarCurrentAcount($sin_pagar, $haber);
            $detalle .= $res['detalle'];
            $haber = $res['haber'];
            $sin_pagar = Self::getFirstSinPagar($client_id, $until_pago);
        }
        // if (!is_null($sin_pagar) && $haber > 0) {
        //     $res = Self::saldarCurrentAcount($sin_pagar, $haber);
        //     $detalle .= $res['detalle'];
        // }
        return $detalle;
    }

    static function saldarSpecificCurrentAcount($to_pay_id, $haber) {
        $current_acount = CurrentAcount::find($to_pay_id);
        Log::info('Pagando especificamente '.$current_acount->detalle.' con $'.$haber);
        $res = Self::saldarCurrentAcount($current_acount, $haber);
        $detalle = $res['detalle'];
        return $detalle;
    }

    static function saldarCurrentAcount($current_acount, $haber) {
        $detalle = '';
        if ($haber >= $current_acount->debe) {
            $current_acount->status = 'pagado';
            $current_acount->save();
            $haber -= $current_acount->debe;
            if (Self::isSaldoInicial($current_acount)) {
                $detalle .= Self::pagadoDetails().' Saldo inicial ';
            } else if (!is_null($current_acount->sale_id)) {
                $detalle .= Self::pagadoDetails().' Rto '.SaleHelper::getNumSaleFromSaleId($current_acount->sale_id).' pag '.$current_acount->page.' ';
            } else if (!is_null($current_acount->budget_id)) {
                $detalle .= Self::pagadoDetails().' Presupuesto '.$current_acount->budget->num;
            }
        } else { 
            if ($current_acount->status == 'pagandose') {
                $current_acount->pagandose += $haber;
            } else {
                $current_acount->status = 'pagandose';
                $current_acount->pagandose = $haber;
            }
            $current_acount->save();
            $haber = 0;
            if (Self::isSaldoInicial($current_acount)) {
                $detalle .= Self::pagandoseDetails().' Saldo inicial ($'.Numbers::price($current_acount->pagandose).')';
            } else if (!is_null($current_acount->sale_id)) {
                $detalle .= Self::pagandoseDetails().' Rto '.SaleHelper::getNumSaleFromSaleId($current_acount->sale_id).' pag '.$current_acount->page.' ($'.Numbers::price($current_acount->pagandose).') ';
            } else if (!is_null($current_acount->budget_id)) {
                $detalle .= Self::pagandoseDetails().' Presupuesto '.$current_acount->budget->num.' ($'.Numbers::price($current_acount->pagandose).') ';
            }
        }
        return [
            'haber'   => $haber,
            'detalle' => $detalle,
        ];
    }

    static function getFirstSinPagar($client_id, $until_pago) {
        $first_current_acount_sin_pagar = CurrentAcount::where('client_id', $client_id)
                                    ->where('status', 'sin_pagar')
                                    ->orderBy('created_at', 'ASC')
                                    ->where('created_at', '<', $until_pago->created_at)
                                    ->first();
        return $first_current_acount_sin_pagar;
    }

    static function getFirstPagandose($client_id, $until_pago) {
        $pagandose = CurrentAcount::where('client_id', $client_id)
                                ->where('status', 'pagandose')
                                ->orderBy('created_at', 'ASC')
                                ->where('created_at', '<', $until_pago->created_at)
                                ->first();
        return $pagandose;
    }

    static function checkSaldos($client_id) {
        $current_acounts = CurrentAcount::where('client_id', $client_id)
                                        ->orderBy('created_at', 'ASC')
                                        ->whereNotNull('debe')
                                        ->update([
                                            'pagandose' => 0,
                                            'status' => 'sin_pagar',
                                        ]);

        $current_acounts = CurrentAcount::where('client_id', $client_id)
                                        ->orderBy('created_at', 'ASC')
                                        ->get();


        foreach ($current_acounts as $current_acount) {
            $saldo = Self::getSaldo($client_id, $current_acount);
            if ($current_acount->debe) {
                $current_acount->saldo = Numbers::redondear($saldo + $current_acount->debe);
                $current_acount->save();
            }
            if ($current_acount->haber) {
                $detalle = Self::procesarPago($current_acount->haber, $current_acount->client_id, $current_acount, $current_acount->to_pay_id);
                $current_acount->saldo = Numbers::redondear($saldo - $current_acount->haber);
                $current_acount->detalle = $detalle;
                $current_acount->save();
            }
        }
    }

    static function checkSaldoInicial($client_id) {
        $saldo_inicial = CurrentAcount::where('client_id', $client_id)
                                        ->where('detalle', 'Saldo inicial')
                                        ->first();
        if (!is_null($saldo_inicial)) {
            if ($saldo_inicial->haber) {
                $saldo_inicial->status = 'pago_from_client';
                $saldo_inicial->saldo = $saldo_inicial->haber;
            } else if ($saldo_inicial->debe) {
                $saldo_inicial->status = 'sin_pagar';
                $saldo_inicial->pagandose = null;
                $saldo_inicial->saldo = $saldo_inicial->debe;
            }
            $saldo_inicial->save();
        } 
        return $saldo_inicial;
    }

    static function isSaldoInicial($current_acount) {
        return $current_acount->detalle == 'Saldo inicial';
    }

    static function getDescription($sale, $total = null) {
        if (count($sale->discounts) >= 1) {
            if (!is_null($total)) {
                $description = '$'.Numbers::price($total);
            } else {
                $description = '$'.Numbers::price(SaleHelper::getTotalSale($sale));
            }
            foreach ($sale->discounts as $discount) {
                $description .= '(-'.$discount->pivot->percentage . '% '. substr($discount->name, 0, 3) .')';
            }
            return $description;
        } else {
            return null;
        }
    }

    static function getCurrentAcountsSinceMonths($client_id, $months_ago) {
        $months_ago = Carbon::now()->subMonths($months_ago);
        $current_acounts = CurrentAcount::where('client_id', $client_id)
                                        ->whereDate('created_at', '>=', $months_ago)
                                        ->orderBy('created_at', 'ASC')
                                        ->with('budget.client.iva_condition')
                                        ->with('budget.observations')
                                        ->with('budget.products.deliveries')
                                        ->with('budget.products.article_stocks')
                                        ->with(['sale' => function($q) {
                                            return $q->withAll();
                                        }])
                                        ->with('payment_method')
                                        ->with('checks')
                                        ->get();
        $current_acounts = Self::format($current_acounts);
        return $current_acounts;
    }

    static function format($current_acounts) {
        foreach ($current_acounts as $current_acount) {
            if (!is_null($current_acount->num_receipt)) {
                $current_acount->numero = 'RP'.Self::getFormatedNum($current_acount->num_receipt);
            }
            if (!is_null($current_acount->sale_id)) {
                $current_acount->numero = 'RT'.Self::getNum('sales', $current_acount->sale_id, 'num_sale');
            }
            if (!is_null($current_acount->budget_id)) {
                $current_acount->numero = 'P'.Self::getNum('budgets', $current_acount->budget_id ,'num');
            }
        }
        return $current_acounts;
    }

    static function getNum($table, $id, $prop) {
        $model = DB::table($table)->where('id', $id)->first();
        return Self::getFormatedNum($model->{$prop});
    }

    static function getFormatedNum($num) {
        $letras_faltantes = 8 - strlen($num);
        $cbte_numero = '';
        for ($i=0; $i < $letras_faltantes; $i++) { 
            $cbte_numero .= '0'; 
        }
        $cbte_numero  .= $num;
        return $cbte_numero;
    }

}