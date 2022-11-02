<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Check;
use App\CurrentAcount;
use App\ErrorCurrentAcount;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\SaleHelper;
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

    static function getSaldo($model_name, $model_id, $until_current_acount) {
        $last = CurrentAcount::orderBy('created_at', 'DESC')
                                ->where('created_at', '<', $until_current_acount->created_at);
        if ($model_name == 'client') {
            $last = $last->where('client_id', $model_id);
        } else {
            $last = $last->where('provider_id', $model_id);
        }
        $last = $last->first();
        if (is_null($last)) {
            return 0;
        } else {
            return $last->saldo;
        }
    }

    static function getProviderSaldo($current_acount) {
        $last = CurrentAcount::where('provider_id', $current_acount->provider_id)
                            ->where('created_at', '<', $current_acount->created_at)
                            ->orderBy('created_at', 'DESC')
                            ->first();
        if (is_null($last)) {
            return 0;
        }
        return $last->saldo;
    }

    static function updateProviderSaldos($current_acount) {
        $following = CurrentAcount::where('provider_id', $current_acount->provider_id)
                                    ->where('created_at', '>', $current_acount->created_at)
                                    ->get();
        foreach ($following as $next) {
            if (!is_null($next->debe)) {
                $next->saldo = Self::getProviderSaldo($next) + $next->debe;
            } else {
                $next->saldo = Self::getProviderSaldo($next) - $next->haber;
            }
            $next->save();
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

    static function procesarPago($model_name, $model_id, $haber, $until_pago, $to_pay_id = null) {
        if (!is_null($to_pay_id)) {
            $until_pago->to_pay_id = $to_pay_id;
            $until_pago->save();
            $detalle = Self::saldarSpecificCurrentAcount($to_pay_id, $haber);
        } else {
            $saldar_pagandose = Self::saldarPagandose($model_name, $model_id, $haber, $until_pago);
            $haber_restante = $saldar_pagandose['haber'];
            $detalle = $saldar_pagandose['detalle'];    
            // Log::info('saldarPagandose detalle: '.$detalle);
            $detalle .= Self::saldarCuentasSinPagar($model_name, $model_id, $haber_restante, $until_pago);
            // Log::info('saldarCuentasSinPagar detalle: '.$detalle);
        }
        return $detalle;
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

    static function notaCredito($haber, $description, $model_name, $model_id, $sale_id = null) {
        $nota_credito = CurrentAcount::create([
            'description'   => $description,
            'haber'         => $haber,
            'status'        => 'nota_credito',
            'client_id'     => $model_name == 'client' ? $model_id : null,
            'provider_id'   => $model_name == 'provider' ? $model_id : null,
            'sale_id'       => $sale_id,
            'user_id'       => UserHelper::userId(),
        ]);
        Log::info('Se guardo nota_credito con id = '.$nota_credito->id.' y sale_id = '.$sale_id);
        $nota_credito->saldo = Self::getSaldo($model_name, $model_id, $nota_credito) - $haber;
        $nota_credito->detalle = 'N.C '.Self::procesarPago($model_name, $model_id, $nota_credito->haber, $nota_credito);
        $nota_credito->save();
        return $nota_credito;
    }

    static function saldarPagandose($model_name, $model_id, $haber, $until_pago) {
        $detalle = '';
        $sin_pagar = Self::getFirstSinPagar($model_name, $model_id, $until_pago);
        $pagandose = Self::getFirstPagandose($model_name, $model_id, $until_pago);
        while (!is_null($sin_pagar) && !is_null($pagandose) && $sin_pagar->created_at->lt($pagandose->created_at) && $haber > 0) {
            $res = Self::saldarCurrentAcount($sin_pagar, $haber);
            $detalle .= $res['detalle'];
            $haber = $res['haber'];
            $sin_pagar = Self::getFirstSinPagar($model_name, $model_id, $until_pago);
            $pagandose = Self::getFirstPagandose($model_name, $model_id, $until_pago);
        }
        while (!is_null($pagandose) && $haber > 0) {
            $haber += $pagandose->pagandose;
            $pagandose->pagandose = 0;
            $pagandose->save();
            $res = Self::saldarCurrentAcount($pagandose, $haber);
            $detalle .= $res['detalle'];
            $haber = $res['haber'];
            $pagandose = Self::getFirstPagandose($model_name, $model_id, $until_pago);
        }
        return [
            'haber'   => $haber,
            'detalle' => $detalle,
        ];
    }

    static function saldarCuentasSinPagar($model_name, $model_id, $haber, $until_pago = null) {
        $detalle = '';
        $sin_pagar = Self::getFirstSinPagar($model_name, $model_id, $until_pago);
        while (!is_null($sin_pagar) && $haber > 0) {
            $res = Self::saldarCurrentAcount($sin_pagar, $haber);
            $detalle .= $res['detalle'];
            $haber = $res['haber'];
            $sin_pagar = Self::getFirstSinPagar($model_name, $model_id, $until_pago);
        }
        // if (!is_null($sin_pagar) && $haber > 0) {
        //     $res = Self::saldarCurrentAcount($sin_pagar, $haber);
        //     $detalle .= $res['detalle'];
        // }
        return $detalle;
    }

    static function saldarSpecificCurrentAcount($to_pay_id, $haber) {
        $current_acount = CurrentAcount::find($to_pay_id);
        if (!is_null($current_acount)) {
            $res = Self::saldarCurrentAcount($current_acount, $haber);
            $detalle = $res['detalle'];
            return $detalle;
        }
        return '';
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
                $detalle .= Self::pagadoDetails().' Rto '.SaleHelper::getNumSaleFromSaleId($current_acount->sale_id).' pag '.$current_acount->page.'. ';
            } else if ($current_acount->detalle == 'Nota de debito') {
                $detalle .= Self::pagadoDetails().' Nota de debito de '.$current_acount->debe.'. ';
                Log::info('se pago nota debito');
            } else if (!is_null($current_acount->budget_id)) {
                $detalle .= Self::pagadoDetails().' Presupuesto '.$current_acount->budget->num.'. ';
            } else if (!is_null($current_acount->provider_order_id)) {
                $detalle .= Self::pagadoDetails().' Pedido '.$current_acount->provider_order->num.'. ';
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
            } else if ($current_acount->detalle == 'Nota de debito') {
                Log::info('pagandose nota debito');
                $detalle .= Self::pagandoseDetails().' Nota de debito de '.$current_acount->debe.'. ';
            } else if (!is_null($current_acount->budget_id)) {
                $detalle .= Self::pagandoseDetails().' Presupuesto '.$current_acount->budget->num.' ($'.Numbers::price($current_acount->pagandose).') ';
            } else if (!is_null($current_acount->provider_order_id)) {
                $detalle .= Self::pagandoseDetails().' Pedido '.$current_acount->provider_order->num.' ($'.Numbers::price($current_acount->pagandose).') ';
            }
        }
        return [
            'haber'   => $haber,
            'detalle' => $detalle,
        ];
    }

    static function getFirstSinPagar($model_name, $model_id, $until_pago) {
        $first = CurrentAcount::where('status', 'sin_pagar')
                                ->orderBy('created_at', 'ASC')
                                ->where('created_at', '<', $until_pago->created_at);
        if ($model_name == 'client') {
            $first = $first->where('client_id', $model_id);
        } else {
            $first = $first->where('provider_id', $model_id);
        }
        $first = $first->first();
        return $first;
    }

    static function getFirstPagandose($model_name, $model_id, $until_pago) {
        $pagandose = CurrentAcount::where('status', 'pagandose')
                                ->orderBy('created_at', 'ASC')
                                ->where('created_at', '<', $until_pago->created_at);
        if ($model_name == 'client') {
            $pagandose = $pagandose->where('client_id', $model_id);
        } else {
            $pagandose = $pagandose->where('provider_id', $model_id);
        }
        $pagandose = $pagandose->first();
        return $pagandose;
    }

    static function checkSaldos($model_name, $model_id) {
        $current_acounts = CurrentAcount::orderBy('created_at', 'ASC')
                                        ->whereNotNull('debe');
        if ($model_name == 'client') {
            $current_acounts = $current_acounts->where('client_id', $model_id);
        } else {
            $current_acounts = $current_acounts->where('provider_id', $model_id);
        }

        $current_acounts->update([
                            'pagandose' => 0,
                            'status' => 'sin_pagar',
                        ]);

        $current_acounts = CurrentAcount::orderBy('created_at', 'ASC');
        if ($model_name == 'client') {
            $current_acounts = $current_acounts->where('client_id', $model_id);
        } else {
            $current_acounts = $current_acounts->where('provider_id', $model_id);
        }
        $current_acounts = $current_acounts->get();

        foreach ($current_acounts as $current_acount) {
            $saldo = Self::getSaldo($model_name, $model_id, $current_acount);
            if (!is_null($current_acount->debe)) {
                $current_acount->saldo = Numbers::redondear($saldo + $current_acount->debe);
                $current_acount->save();
            }
            if (!is_null($current_acount->haber)) {
                $detalle = '';
                if ($current_acount->status == 'nota_credito') {
                    $detalle = 'Nota Credito ';
                } 
                $detalle .= Self::procesarPago($model_name, $model_id, $current_acount->haber, $current_acount, $current_acount->to_pay_id);
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

    static function getCurrentAcountsSinceMonths($model_name, $model_id, $months_ago) {
        $months_ago = Carbon::now()->subMonths($months_ago);
        $current_acounts = CurrentAcount::whereDate('created_at', '>=', $months_ago)
                                        ->orderBy('created_at', 'ASC')
                                        ->with(['sale' => function($q) {
                                            return $q->withAll();
                                        }])
                                        ->with(['budget' => function($q) {
                                            return $q->withAll();
                                        }])
                                        ->with('payment_method')
                                        ->with('checks');
        if ($model_name == 'client') {
            $current_acounts = $current_acounts->where('client_id', $model_id);
        } else {
            $current_acounts = $current_acounts->where('provider_id', $model_id);
        }
        $current_acounts = $current_acounts->get();
        $current_acounts = Self::format($current_acounts);
        return $current_acounts;
    }

    static function format($current_acounts) {
        foreach ($current_acounts as $current_acount) {
            if (!is_null($current_acount->num_receipt)) {
                $current_acount->numero = 'ReciboPago '.$current_acount->num_receipt;
                // $current_acount->numero = 'ReciboPago'.Self::getFormatedNum($current_acount->num_receipt);
            }
            if (!is_null($current_acount->sale_id)) {
                $current_acount->numero = 'Remito N°'.Self::getNum('sales', $current_acount->sale_id, 'num_sale');
            }
            if (!is_null($current_acount->budget_id)) {
                $current_acount->numero = 'Presupuesto N°'.Self::getNum('budgets', $current_acount->budget_id ,'num');
            }
            if (!is_null($current_acount->provider_order_id)) {
                $current_acount->numero = 'Pedido N°'.Self::getNum('provider_orders', $current_acount->provider_order_id ,'num');
            }
            if (!is_null($current_acount->order_production_id)) {
                $current_acount->numero = 'Orden de produccion N°'.Self::getNum('order_productions', $current_acount->order_production_id ,'num');
            }
            if ($current_acount->status == 'nota_credito') {
                $current_acount->numero = 'NotaCredito';
            }
            if ($current_acount->detalle == 'Saldo inicial') {
                $current_acount->numero = 'Saldo inicial';
            }
            if ($current_acount->detalle == 'Nota de debito') {
                $current_acount->numero = 'Nota debito';
            }
        }
        return $current_acounts;
    }

    static function getNum($table, $id, $prop) {
        $model = DB::table($table)->where('id', $id)->first();
        return $model->{$prop};
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