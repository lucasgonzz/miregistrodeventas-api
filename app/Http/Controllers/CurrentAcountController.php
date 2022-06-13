<?php

namespace App\Http\Controllers;

use App\Commissioner;
use App\CurrentAcount;
use App\Hola;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintCurrentAcounts;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Imports\CurrentAcountsImport;
use App\Sale;
use App\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CurrentAcountController extends Controller
{

    public function pagoFromClient(Request $request) {
        $data = [
            'haber'                             => $request->haber,
            'client_id'                         => $request->client_id,
            'current_date'                      => $request->current_date,
            'created_at'                        => $request->created_at,
            'current_acount_payment_method_id'  => $request->current_acount_payment_method_id,
            'checks'                            => $request->checks,
            'to_pay'                            => $request->to_pay,
        ];
        $pago = CurrentAcountHelper::pagoFromClient($data);
        return response()->json(['current_acount' => $pago], 201);
    }

    public function notaCredito(Request $request) {
        $nota_credito = CurrentAcountHelper::notaCredito($request->form['nota_credito'], $request->form['description'], $request->client_id);
        return response()->json(['current_acount' => $nota_credito], 201);
    }

    function updateDebe(Request $request) {
        $current_acount = CurrentAcount::find($request->id);
        $current_acount->debe = $request->debe;
        $current_acount->save();
        return response(null, 200);
        // $client_controller = new ClientController();
        // $client_controller->checkSaldoss($current_acount->client_id);
    }

    function pdfFromClient($client_id, $months_ago) {
        $pdf = new PdfPrintCurrentAcounts(null, $client_id, $months_ago);
        $pdf->printCurrentAcounts();
    }

    function pdf($ids) {
        $pdf = new PdfPrintCurrentAcounts(explode('-', $ids));
        $pdf->printCurrentAcounts();
    }

    function checkPagos($client_id) {
        $current_acounts_pagadas = CurrentAcount::where(function($query) use ($client_id) {
                                            $query->where('client_id', $client_id)
                                                ->where('status', 'pagandose');
                                        })
                                        ->orWhere(function($query) use ($client_id) {
                                            $query->where('client_id', $client_id)
                                                ->where('status', 'pagado');
                                        })
                                        ->get();
        foreach ($current_acounts_pagadas as $current_acount) {
            $current_acount->update([
                'status' => 'sin_pagar',
                'pagandose' => null,
            ]);
        }
        $pagos = CurrentAcount::where([['client_id', $client_id], ['status', 'pago_from_client']])
                                ->orWhere([['client_id', $client_id], ['status', 'nota_credito']])
                                ->orderBy('created_at', 'ASC')
                                ->get();
        foreach ($pagos as $pago) {
            $detalle = $this->procesarPago($pago->haber, $client_id, $pago);
            $pago->detalle = $detalle;
            $pago->save();
        }
    }

    

    function deleteFromSale($sale) {
        $current_acounts = CurrentAcount::where('sale_id', $sale->id)
                                        ->pluck('id');
        CurrentAcount::destroy($current_acounts);
    }

    function updateSaldo($client_id, $current_acounts) {
        foreach ($current_acounts as $current_acount) {
            if ($this->esUnPago($current_acount)) {
                $current_acount->saldo = CurrentAcountHelper::getSaldo($client_id, $current_acount) - $current_acount->haber;
            } else {
                $current_acount->saldo = CurrentAcountHelper::getSaldo($client_id, $current_acount) + $current_acount->debe;
            }
            $current_acount->save();
        }
    }

    function esUnPago($current_acount) {
        return $current_acount->status == 'pago_from_client' || $current_acount->status == 'nota_credito';
    }

    function import(Request $request, $client_id) {
        Excel::import(new CurrentAcountsImport($client_id), $request->file('current_acounts'));
        $errors = Hola::all();
        Log::info('sdf: ');
        Log::info($errors);
        return response(null, 200);
    }

    function delete($id) {
        $current_acount = CurrentAcount::find($id);
        $current_acount->delete();
        CurrentAcountHelper::restartCurrentAcounts($current_acount->client_id);
    }
}
