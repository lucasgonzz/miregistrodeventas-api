<?php

namespace App\Http\Controllers;

use App\Commissioner;
use App\CurrentAcount;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintCurrentAcounts;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Sale;
use App\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CurrentAcountController extends Controller
{
    public function store($sale) {
    	if ($sale->client_id) {
            $total_articles = count($sale->articles);
            if ($total_articles > 30) {
                $articulos_en_pagina = 0;
                $articulos_en_venta = 0;
                $pag = 0;
                $total = 0;
                foreach ($sale->articles as $article) {
                    $articulos_en_venta++;
                    if ($articulos_en_pagina < 30 && $articulos_en_venta < $total_articles) {
                        $total += (float)$article->pivot->price * (int)$article->pivot->amount;
                        $articulos_en_pagina++;
                    } else {
                        $pag++;
                        if ($articulos_en_venta >= $total_articles) {
                            $total += (float)$article->pivot->price * (int)$article->pivot->amount;
                        }
                        $total_original = $total;
                        if (!is_null($sale->discounts)) {
                            $total = SaleHelper::getTotalMenosDescuentos($sale, $total);
                        }
                        $current_acount = CurrentAcount::create([
                            'detalle'     => 'Rto '.$sale->num_sale.' pag '.$pag,
                            'debe'        => $total,
                            'saldo'       => CurrentAcountHelper::getSaldo($sale->client_id) + $total,
                            'status'      => 'sin_pagar',
                            'client_id'   => $sale->client_id,
                            'seller_id'   => $sale->client->seller_id,
                            'sale_id'     => $sale->id,
                            'description' => CurrentAcountHelper::getDescription($sale, $total_original),
                        ]);
                        $articulos_en_pagina = 0;
                        $articulos_en_pagina++;
                        $total = 0;
                        $total += (float)$article->pivot->price * (int)$article->pivot->amount;
                    }
                }
                // $current_acount->detalle = $articulos_en_venta;
                // $current_acount->save();
            } else {
                if (!is_null($sale->discounts)) {
                    $debe = SaleHelper::getPrecioConDescuento($sale);
                } else {
                    $debe = SaleHelper::getTotalSale($sale);
                }
    	    	$current_acount = CurrentAcount::create([
    	    		'detalle'     => 'Rto '.$sale->num_sale,
    	    		'debe'        => $debe,
    	    		'saldo'       => CurrentAcountHelper::getSaldo($sale->client_id) + $debe,
    	    		'status'      => 'sin_pagar',
                    'client_id'   => $sale->client_id,
                    'seller_id'   => $sale->client->seller_id,
    	    		'sale_id'     => $sale->id,
                    'description' => CurrentAcountHelper::getDescription($sale),
    	    	]);
            }
    	}
    }

    function updateDebe(Request $request) {
        $current_acount = CurrentAcount::find($request->id);
        $current_acount->debe = $request->debe;
        $current_acount->save();
        $client_controller = new ClientController();
        $client_controller->checkSaldos($current_acount->client_id);
    }

    function pdf($client_id, $months_ago) {
        $pdf = new PdfPrintCurrentAcounts($client_id, $months_ago);
        $pdf->printCurrentAcounts();
    }

    function checkPagos($client_id) {
        $current_acounts_pagadas = CurrentAcount::where('client_id', $client_id)
                                        ->where('status', 'pagado')
                                        ->orWhere('status', 'pagandose')
                                        ->get();
        foreach ($current_acounts_pagadas as $current_acount) {
            $current_acount->update([
                'status' => 'sin_pagar',
                'pagandose' => null,
            ]);
        }
        $pagos = CurrentAcount::where([['client_id', $client_id], ['status', 'pago_from_client']])
                                ->orWhere([['client_id', $client_id], ['status', 'nota_credito']])
                                ->orderBy('id', 'ASC')
                                ->get();
        foreach ($pagos as $pago) {
            $saldar_pagandose = $this->saldarPagandose($pago->haber, $client_id, $pago);
            $detalle = $saldar_pagandose['detalle'];
            $detalle .= $this->saldarCuentasSinPagar($saldar_pagandose['pago'], $client_id, $pago);
            $pago->detalle = $detalle;
            $pago->save();
        }
    }

    public function pagoFromClient(Request $request) {
        $saldar_pagandose = $this->saldarPagandose($request->pago, $request->client_id);
        $pago = $saldar_pagandose['pago'];
        $detalle = $saldar_pagandose['detalle'];
        $detalle .= $this->saldarCuentasSinPagar($pago, $request->client_id);
        $pago = CurrentAcount::create([
            'detalle'   => $detalle,
            'haber'     => $request->pago,
            'saldo'     => CurrentAcountHelper::getSaldo($request->client_id) - $request->pago,
            'status'    => 'pago_from_client',
            'client_id' => $request->client_id,
        ]);
        return response()->json(['current_acount' => $pago], 201);
    }

    public function notaCredito(Request $request) {
        $saldar_pagandose = $this->saldarPagandose($request->form['nota_credito'], $request->client_id);
        $pago = $saldar_pagandose['pago'];
        $detalle = $saldar_pagandose['detalle'];
        $detalle .= $this->saldarCuentasSinPagar($pago, $request->client_id);
        $nota_credito = CurrentAcount::create([
            'detalle'       => 'N.C. '.$detalle,
            'description'   => $request->form['description'],
            'haber'         => $request->form['nota_credito'],
            'saldo'         => CurrentAcountHelper::getSaldo($request->client_id) - $request->form['nota_credito'],
            'status'        => 'nota_credito',
            'client_id'     => $request->client_id,
        ]);
        return response()->json(['current_acount' => $nota_credito], 201);
    }

    // function sellerCommissions($seller_id) {
    //     $seller_of_seller = Seller::where('seller_id', $seller_id)->first();
    //     $commissioner = Commissioner::where('seller_id', $seller_id)->first();
    //     $current_acounts = CurrentAcount::where(function($q) use ($seller_id) {
    //                                         $q->where('seller_id', $seller_id)
    //                                         ->where('status', 'pagado');
    //                                     })
    //                                     ->orWhere(function($q) use ($commissioner) {
    //                                         $q->where('commissioner_id', $commissioner->id)
    //                                             ->where('status', 'pago_for_commissioner');
    //                                     })
    //                                     ->orWhere(function($query) use ($seller_of_seller) {
    //                                         if (!is_null($seller_of_seller)) {
    //                                             $query->where('status', 'pagado')
    //                                                 ->where('seller_id', $seller_of_seller->id);
    //                                         }
    //                                     })
    //                                     ->whereDate('created_at', '>=', Carbon::now()->subMonth())
    //                                     ->with('sale.commissioners')
    //                                     ->with('sale.discounts')
    //                                     ->with('client')
    //                                     ->with('seller')
    //                                     ->orderBy('created_at', 'DESC')
    //                                     ->get();
    //     foreach ($current_acounts as $key => $current_acount) {
    //         if (!is_null($current_acount->seller) && !is_null($current_acount->seller->seller_id) && !is_null($seller_of_seller) && $current_acount->seller->id == $seller_of_seller->id && DiscountHelper::getTotalDiscountsPercentage($current_acount->sale->discounts) >= 10) {
    //             $current_acounts->forget($key);
    //         }
    //     }
    //     $res = [];
    //     foreach ($current_acounts as $current_acount) {
    //         $res[] = $current_acount;
    //     }
    //     return response()->json(['current_acounts' => $res], 200);
    // }

    // function oscarFedePapiCommissions($commissioner_id) {
    //     // $seller_id = Commissioner::find($commissioner_id)->seller_id;
    //     $current_acounts = CurrentAcount::where([
    //                                         ['status', '!=', 'saldo_inicial'],
    //                                         ['status', '!=', 'nota_credito'],
    //                                         ['status', '!=', 'pago_from_client'],
    //                                         ['status', '!=', 'pago_for_commissioner'],
    //                                     ])
    //                                     ->orWhere(function($q) use ($commissioner_id) {
    //                                         $q->where('status', 'pago_for_commissioner')
    //                                         ->where('commissioner_id', $commissioner_id);
    //                                     })
    //                                     ->with('sale.commissioners')
    //                                     ->with('sale.discounts')
    //                                     ->orderBy('created_at', 'DESC')
    //                                     ->with('client')
    //                                     ->with('seller')
    //                                     ->get();
    //     return response()->json(['current_acounts' => $current_acounts], 200);
    // }

    // function perdidasCommissions() {
    //     // $seller_id = Commissioner::find($commissioner_id)->seller_id;
    //     $perdidas = Commissioner::where('name', 'Perdidas')->first();
    //     $current_acounts = CurrentAcount::where([
    //                                         ['status', '!=', 'saldo_inicial'],
    //                                         ['status', '!=', 'nota_credito'],
    //                                         ['status', '!=', 'pago_from_client'],
    //                                         ['status', '!=', 'pago_for_commissioner'],
    //                                     ])
    //                                     ->whereHas('client', function($q) {
    //                                         $q->whereNull('seller_id');
    //                                     })
    //                                     ->orWhere(function($q) use ($perdidas) {
    //                                         $q->where('status', 'pago_for_commissioner')
    //                                         ->where('commissioner_id', $perdidas->id);
    //                                     })
    //                                     ->with('sale.commissioners')
    //                                     ->orderBy('created_at', 'DESC')
    //                                     ->with('sale.discounts')
    //                                     ->with('client')
    //                                     ->with('seller')
    //                                     ->get();
    //     return response()->json(['current_acounts' => $current_acounts], 200);
    // }

    public function saldarCuentasSinPagar($pago, $client_id, $until_pago = null) {
        $detalle = '';
        $current_acount_sin_pagar = $this->getFirstCurrentAcountSinPagar($client_id, $until_pago);
        while (!is_null($current_acount_sin_pagar) && $pago >= $current_acount_sin_pagar->debe) {
            $pago -= $current_acount_sin_pagar->debe;
            $current_acount_sin_pagar->status = 'pagado';
            $current_acount_sin_pagar->save();
            $commission_controller = new CommissionController();
            $commission_controller->updateToActive($current_acount_sin_pagar);
            if (CurrentAcountHelper::isSaldoInicial($current_acount_sin_pagar)) {
                $detalle .= 'A cta saldo saldo inicial ';
            } else {
                $detalle .= 'A cta saldo Rto '.SaleHelper::getNumSaleFromSaleId($current_acount_sin_pagar->sale_id).' pag '.$current_acount_sin_pagar->page.' ';
            }
            $current_acount_sin_pagar = $this->getFirstCurrentAcountSinPagar($client_id, $until_pago);
        }
        if (!is_null($current_acount_sin_pagar) && $pago > 0) {
            $current_acount_sin_pagar->status = 'pagandose';
            $current_acount_sin_pagar->pagandose = $pago;
            $current_acount_sin_pagar->save();
            if (CurrentAcountHelper::isSaldoInicial($current_acount_sin_pagar)) {
                $detalle .= 'A cta saldo inicial ($'.Numbers::price($pago).')';
            } else {
                $detalle .= 'A cta Rto '.SaleHelper::getNumSaleFromSaleId($current_acount_sin_pagar->sale_id).' pag '.$current_acount_sin_pagar->page.' ($'.Numbers::price($pago).') ';
            }
        }
        return $detalle;
    }

    public function saldarPagandose($pago, $client_id, $until_pago = null) {
        $detalle = '';
        $query = CurrentAcount::where('client_id', $client_id)
                                ->where('status', 'pagandose')
                                ->orderBy('id', 'ASC');
        if (!is_null($until_pago)) {
            $pagandose = $query->where('id', '<', $until_pago->id)
                                                    ->first();
        } else {
            $pagandose = $query->first();
        }
        if (!is_null($pagandose)) {
            echo "Se estaba pagando la cuenta corriente: ".$pagandose->detalle."</br>";
            $pago += $pagandose->pagandose;
            if ($pago >= $pagandose->debe) {
                $pago -= $pagandose->debe;
                $pagandose->status = 'pagado';
                $commission_controller = new CommissionController();
                $commission_controller->updateToActive($pagandose);
                if (CurrentAcountHelper::isSaldoInicial($pagandose)) {
                    $detalle .= 'A cta saldo saldo inicial ';
                } else {
                    $detalle = 'A cta Saldo Rto '.SaleHelper::getNumSaleFromSaleId($pagandose->sale_id).' pag '.$pagandose->page.' ';
                }
            } else {
                $pagandose->pagandose += $pago;
                if (CurrentAcountHelper::isSaldoInicial($pagandose)) {
                    $detalle .= 'A cta saldo inicial ($'.Numbers::price($pago).')';
                } else {
                    $detalle = 'A cta Rto '.SaleHelper::getNumSaleFromSaleId($pagandose->sale_id).' pag '.$pagandose->page.' ($'.Numbers::price($pago).') ';
                }
                $pago = 0;
            }
            $pagandose->save();
        }
        return [
            'pago'    => $pago,
            'detalle' => $detalle,
        ];
    }

    public function getFirstCurrentAcountSinPagar($client_id, $until_pago) {
        $query = CurrentAcount::where('client_id', $client_id)
                                    ->where('status', 'sin_pagar')
                                    ->orderBy('id', 'ASC');
        if (!is_null($until_pago)) {
            $first_current_acount_sin_pagar = $query->where('id', '<', $until_pago->id)
                                                    ->first();
        } else {
            $first_current_acount_sin_pagar = $query->first();
        }
        return $first_current_acount_sin_pagar;
    }

    // public function getSellerSaldo($seller_id) {
    //     $current_acount = CurrentAcount::where('seller_id', $seller_id)
    //                             ->where('status', 'pago_for_seller')
    //                             ->orderBy('created_at', 'DESC')
    //                             ->first();
    //     if (is_null($current_acount)) {
    //         $current_acounts = CurrentAcount::where('seller_id', $seller_id)
    //                                         ->where('status', 'pagado')
    //                                         ->get();
    //         $saldo = 0;
    //         foreach ($current_acounts as $current_acount) {
    //             $saldo += $current_acount->debe;
    //         }
    //         return $saldo;
    //     } else {
    //         return $current_acount->saldo;
    //     }
    // }

    // function borrarCurrentAcountsQueQuedanSinUtilizar($index, $current_acounts) {
    //     if ($index < count($current_acounts)) {
    //         for ($i=$index; $i < count($current_acounts); $i++) { 
    //             $current_acount_to_delete = $current_acounts[$i];
    //             $current_acount_to_delete->delete();
    //         }
    //     }
    // }

    function delete($id) {
        $current_acount_to_delete = CurrentAcount::find($id);
        $current_acounts_que_siguen = CurrentAcount::where('client_id', $current_acount_to_delete->client_id)
                                                ->where('id', '>', $current_acount_to_delete->id)
                                                ->orderBy('created_at', 'ASC')
                                                ->get();
        $current_acount_to_delete->delete();
        $this->updateSaldo($current_acount_to_delete->client_id, $current_acounts_que_siguen);
    }

    function deleteFromSale($sale) {
        $current_acounts_to_delete = CurrentAcount::where('sale_id', $sale->id)
                                                    ->get();

        if (count($current_acounts_to_delete) >= 1) {
            $ultima_a_eliminar = $current_acounts_to_delete[count($current_acounts_to_delete)-1];
            $current_acounts_que_siguen = CurrentAcount::where('client_id', $sale->client_id)
                                                    ->where('id', '>', $ultima_a_eliminar->id)
                                                    ->orderBy('created_at', 'ASC')
                                                    ->get();
            foreach ($current_acounts_to_delete as $current_acount_to_delete) {
                $current_acount_to_delete->delete();
            }
            $this->updateSaldo($sale->client_id, $current_acounts_que_siguen);
        }
    }

    // function getDetalleForUpdated($sale, $page, $index) {
    //     return "Rtoo ".count($sale->articles)." $index ".$sale->num_sale.' pag '.$page;
    // }

    // function updateCurrentAcountsQueSiguen($since_current_acount) {
    //     $current_acounts_que_siguen = CurrentAcount::where('client_id', $since_current_acount->client_id)
    //                                             ->where('id', '>', $since_current_acount->id)
    //                                             ->orderBy('created_at', 'ASC')
    //                                             ->get();
    //     $this->updateSaldo($since_current_acount->client_id, $current_acounts_que_siguen);
    // }

    function updateSaldo($client_id, $current_acounts) {
        foreach ($current_acounts as $current_acount) {
            $current_acount->saldo = CurrentAcountHelper::getSaldo($client_id, $current_acount) + $current_acount->debe;
            $current_acount->save();
        }
    }
}
