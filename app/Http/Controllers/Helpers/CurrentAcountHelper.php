<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\CurrentAcount;
use App\ErrorCurrentAcount;
use App\Hola;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CurrentAcountHelper {

    static function getSaldo($client_id, $until_current_acount) {
        // Log::
        $last_current_acount = CurrentAcount::where('client_id', $client_id)
                                ->orderBy('created_at', 'DESC')
                                ->where('created_at', '<', $until_current_acount->created_at)
                                ->first();
        if (is_null($last_current_acount)) {
            return 0;
        } else {
            return $last_current_acount->saldo;
        }
    }

    static function restartCurrentAcounts($client_id) {
        CurrentAcount::where('client_id', $client_id)
                    ->whereNotNull('sale_id')
                    ->update([
                        'status' => 'sin_pagar'
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
            'created_at' => $sale->created_at,
        ]);
        Log::info('Se creo remito para venta sin articulos: '.$current_acount->detalle);
    }

    static function pagoFromClient($haber, $client_id, $current_date, $created_at = null) {
        $pago = CurrentAcount::create([
            'haber'         => $haber,
            'status'        => 'pago_from_client',
            'client_id'     => $client_id,
            'created_at'    => $current_date ? Carbon::now() : $created_at,
        ]);
        $pago->saldo = Self::getSaldo($pago->client_id, $pago) - $haber;
        $pago->detalle = Self::procesarPago($pago->haber, $pago->client_id, $pago);
        $pago->save();
        // if (!$current_date) {
        //     Self::checkSaldos($pago->client_id);
        // }
        return $pago;
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

    static function procesarPago($haber, $client_id, $until_pago = null) {
        $saldar_pagandose = Self::saldarPagandose($haber, $client_id, $until_pago);
        $haber_restante = $saldar_pagandose['haber'];
        $detalle = $saldar_pagandose['detalle'];    
        // Log::info('detalle de saldar_pagandose:');
        // Log::info($detalle);
        $detalle .= Self::saldarCuentasSinPagar($haber_restante, $client_id, $until_pago);
        // Log::info('DETALLE:');
        // Log::info($detalle);
        return $detalle;
    }

    static function saldarPagandose($haber, $client_id, $until_pago) {
        $detalle = '';
        $query = CurrentAcount::where('client_id', $client_id)
                                ->where('status', 'pagandose')
                                ->orderBy('created_at', 'ASC');
        if (!is_null($until_pago)) {
            $pagandose = $query->where('created_at', '<', $until_pago->created_at)
                                                    ->first();
        } else {
            $pagandose = $query->first();
        }
        if (!is_null($pagandose)) {
            $haber += $pagandose->pagandose;
            if ($haber >= $pagandose->debe) {
                $haber -= $pagandose->debe;
                $pagandose->status = 'pagado';
                $commission_controller = new CommissionController();
                $commission_controller->updateToActive($pagandose);
                if (Self::isSaldoInicial($pagandose)) {
                    $detalle .= 'A cta saldo Saldo inicial ';
                } else {
                    $detalle = 'A cta Saldo Rto '.SaleHelper::getNumSaleFromSaleId($pagandose->sale_id).' pag '.$pagandose->page.' ';
                }
            } else {
                $pagandose->pagandose = $haber;
                if (Self::isSaldoInicial($pagandose)) {
                    $detalle .= 'A cta saldo inicial ($'.Numbers::price($haber).')';
                } else {
                    $detalle = 'A cta Rto '.SaleHelper::getNumSaleFromSaleId($pagandose->sale_id).' pag '.$pagandose->page.' ($'.Numbers::price($haber).') ';
                }
                $haber = 0;
            }
            $pagandose->save();
        }
        return [
            'haber'    => $haber,
            'detalle' => $detalle,
        ];
    }

    static function saldarCuentasSinPagar($haber, $client_id, $until_pago = null) {
        $detalle = '';
        $current_acount_sin_pagar = Self::getFirstCurrentAcountSinPagar($client_id, $until_pago);
        while (!is_null($current_acount_sin_pagar) && $haber >= $current_acount_sin_pagar->debe) {
            $haber -= $current_acount_sin_pagar->debe;
            $current_acount_sin_pagar->status = 'pagado';
            $current_acount_sin_pagar->save();
            $commission_controller = new CommissionController();
            $commission_controller->updateToActive($current_acount_sin_pagar);
            if (Self::isSaldoInicial($current_acount_sin_pagar)) {
                $detalle .= 'A cta saldo Saldo inicial ';
            } else {
                $detalle .= 'A cta saldo Rto '.SaleHelper::getNumSaleFromSaleId($current_acount_sin_pagar->sale_id).' pag '.$current_acount_sin_pagar->page.' ';
            }
            $current_acount_sin_pagar = Self::getFirstCurrentAcountSinPagar($client_id, $until_pago);
        }
        if (!is_null($current_acount_sin_pagar) && $haber > 0) {
            $current_acount_sin_pagar->status = 'pagandose';
            $current_acount_sin_pagar->pagandose = $haber;
            $current_acount_sin_pagar->save();
            if (Self::isSaldoInicial($current_acount_sin_pagar)) {
                $detalle .= 'A cta Saldo inicial ($'.Numbers::price($haber).')';
            } else {
                $detalle .= 'A cta Rto '.SaleHelper::getNumSaleFromSaleId($current_acount_sin_pagar->sale_id).' pag '.$current_acount_sin_pagar->page.' ($'.Numbers::price($haber).') ';
            }
        }
        return $detalle;
    }

    static function getFirstCurrentAcountSinPagar($client_id, $until_pago) {
        $query = CurrentAcount::where('client_id', $client_id)
                                    ->where('status', 'sin_pagar')
                                    ->orderBy('created_at', 'ASC');
        if (!is_null($until_pago)) {
            $first_current_acount_sin_pagar = $query->where('created_at', '<', $until_pago->created_at)
                                                    ->first();
        } else {
            $first_current_acount_sin_pagar = $query->first();
        }
        return $first_current_acount_sin_pagar;
    }

    static function saveErrors($client_id, $messages) {
        foreach ($messages as $message) {
            $errors = CurrentAcount::create([
                'detalle'       => $message,
                'client_id'     => $client_id,
                'status'        => 'pago_from_client',
            ]);
            Hola::create([
                'text'       => $message,
                'client_id'     => $client_id,
            ]);
        }   
    }

    static function checkSaldos($client_id) {
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
                // $detalle = Self::procesarPago($current_acount->haber, $client_id, $current_acount);
                // Log::info('detalle:');
                // Log::info($detalle);
                // $current_acount->detalle = $detalle;
                $current_acount->saldo = Numbers::redondear($saldo - $current_acount->haber);
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
                                        ->get();
        return $current_acounts;
    }

    // static function getCurrentAcountsSinceMonths($client_id, $months_ago) {
    //     $months_ago = Carbon::now()->subMonths($months_ago);
    //     $current_acounts = CurrentAcount::where('client_id', $client_id)
    //                                     ->whereDate('created_at', '>=', $months_ago)
    //                                     ->orderBy('sale_id', 'ASC')
    //                                     ->get();
    //     return $current_acounts;
    // }

}