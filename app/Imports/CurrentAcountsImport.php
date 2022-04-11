<?php

namespace App\Imports;

use App\CurrentAcount;
use App\ErrorCurrentAcount;
use App\Hola;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class CurrentAcountsImport implements ToCollection
{

    public $client_id;
    public $current_acounts;

    public function  __construct($client_id) {
        $this->client_id= $client_id;
        $this->current_acounts = [];
        $this->messages = [];
        $this->saldo_inicial = null;
        // $this->saldo_inicial = CurrentAcountHelper::checkSaldoInicial($this->client_id);
    }

    public function collection(Collection $rows) {
        Log::info('Se llamo el metodo');
        $this->clearCurrentAcounts();

        $this->setCurrentAcounts($rows);

        $this->setFirstSale();

        $this->setSaldoInicial();

        $this->setFromSaldoInicial();

        $this->setSalesCreatedAt();

        CurrentAcountHelper::createCurrentAcountsFromSales($this->client_id, $this->from_sale);

        $this->compareCurrentAcountsFromSalesWithExel();
        
        $this->checkDates();

        $this->createPagos();

        CurrentAcountHelper::checkSaldos($this->client_id);

        CurrentAcountHelper::saveErrors($this->client_id, $this->messages);
        exit;
        return null;
    }

    function setFromSaldoInicial() {
        $current_acounts = [];
        foreach ($this->current_acounts as $current_acount) {
            if ($current_acount['created_at']->gt($this->saldo_inicial->created_at)) {
                $current_acounts[] = $current_acount;
            }
        }
        $this->current_acounts = $current_acounts;
    }

    function createPagos() {
        foreach ($this->current_acounts as $current_acount) {
            $haber = $current_acount['haber'];
            if (!is_null($haber)) {
                Log::info('Creando pago del '.$current_acount['created_at']->format('d/m/Y').' por '.$current_acount['haber'].' con saldo de '.$current_acount['saldo']);
                CurrentAcountHelper::pagoFromClient($haber, $this->client_id, false, $current_acount['created_at']);
            }
        }
    }

    function clearCurrentAcounts() {
        $current_acounts = CurrentAcount::where('client_id', $this->client_id)
                                        ->pluck('id');
        CurrentAcount::destroy($current_acounts);
    }

    function setFirstSale() {
        $this->first_sale = Sale::where('client_id', $this->client_id)
                                ->orderBy('created_at', 'ASC')
                                ->first();
        Log::info('First sale: '.$this->first_sale->num_sale.' del '.$this->first_sale->created_at->format('d/m/Y'));
    }

    function setSaldoInicial() {
        foreach ($this->current_acounts as $index => $current_acount) {
            if ($this->getNumSale($current_acount) == $this->first_sale->num_sale) {
                // Log::info('Entro con: '.$current_acount['detalle']);
                $previus = $this->current_acounts[$index-1];
                // Log::info('Se va a usar: '.$previus['detalle'].' - '.$previus['created_at']->format('d/m/Y'));
                // Log::info($previus);
                $this->saldo_inicial = CurrentAcount::create([
                    'detalle'       => 'Saldo inicial',
                    'status'        => !is_null($previus['debe']) ? 'sin_pagar' : 'pago_from_client',
                    'client_id'     => $this->client_id,
                    'debe'          => $previus['debe'],
                    'haber'         => $previus['haber'],
                    'saldo'         => $previus['saldo'],
                    'created_at'    => $previus['created_at']->endOfDay(),
                ]);
                $this->from_sale = $this->saldo_inicial;
                break;
            }
        }
        if (is_null($this->saldo_inicial)) {
            foreach ($this->current_acounts as $index => $current_acount) {
                if ($current_acount['created_at']->gte($this->first_sale->created_at)) {
                    // Log::info('Entro con: '.$current_acount['detalle']);
                    $previus = $this->current_acounts[$index-1];
                    // Log::info('Se va a usar: '.$previus['detalle'].' - '.$previus['created_at']->format('d/m/Y'));
                    // Log::info($previus);
                    $this->saldo_inicial = CurrentAcount::create([
                        'detalle'       => 'Saldo inicial',
                        'status'        => !is_null($previus['debe']) ? 'sin_pagar' : 'pago_from_client',
                        'client_id'     => $this->client_id,
                        'debe'          => $previus['debe'],
                        'haber'         => $previus['haber'],
                        'saldo'         => $previus['saldo'],
                        'created_at'    => $previus['created_at']->endOfDay(),
                    ]);
                    $this->from_sale = $current_acount;
                    break;
                }
            }
            Log::info('Se van a crear las cuentas correiten de las ventas a partir de '.$this->from_sale['created_at']);
        }
    }

    function getNumSale($current_acount, $from_object = false) {
        if ($from_object) {
            return substr($current_acount->detalle, 3);
        }
        return substr($current_acount['detalle'], 3);
    }

    function getPage($current_acount) {
        $detalle = $current_acount['detalle'];
        if (str_contains($detalle, 'Hoja')) {
            $index = strpos($detalle, 'a');
            $page = substr($detalle, $index + 1);
            return $page;
        } else {
            return 1;
        }
    }

    function setCurrentAcounts($rows) {
        $index = 0;
        $id = 1;
        foreach ($rows as $row) {
            if ($index >= 9) {
                if ($row[0] != '') {
                    $created_at = $this->getCreatedAt($row[0]);
                    $new_current_acount['created_at'] = $created_at;
                    $new_current_acount['debe'] = null;
                    $new_current_acount['haber'] = null;
                    $new_current_acount['id'] = $id;
                }
                $detalle = $row[1].$row[2];
                $debe = $row[3];
                $haber = $row[4];
                $saldo = $row[5];
                if ($debe != '') {
                    $new_current_acount['debe'] = $debe;
                }
                if ($haber != '') {
                    $new_current_acount['haber'] = $haber;
                }
                if ($debe != '' || $haber != '') {
                    $new_current_acount['detalle'] = $detalle;
                    $new_current_acount['saldo'] = $saldo;
                    $this->current_acounts[] = $new_current_acount;
                    $id++;
                }
            }
            $index++;
        }
    }
    

    function getFirstCreatedAt() {
        $first_current_acount = CurrentAcount::where('client_id', $this->client_id)
                                            ->orderBy('created_at', 'ASC')
                                            ->first();
        if (is_null($first_current_acount)) {
            $first_created_at = Carbon::now()->subYear();
        } else {
            $first_created_at = $first_current_acount->created_at;
        }
        return $first_created_at;
    }

    function checkDates() {
        $last_date = '';
        $same_date = [];
        $previus_added = false;
        foreach ($this->current_acounts as $index => $current_acount) {
            if ($last_date == $current_acount['created_at']->format('Y-m-d')) {
                if (!$previus_added) {
                    $same_date[] = $this->current_acounts[$index-1];
                    $previus_added = true;
                }
                $same_date[] = $current_acount;
            } else {
                $last_date = $current_acount['created_at']->format('Y-m-d');
                $this->setSameDate($same_date);
                $previus_added = false;
                $same_date = [];
            }
        }
        // $this->checkFirst();
        // foreach ($this->current_acounts as $c_a) {
        //     $fecha = $c_a['created_at']->format('d/m/Y H:i:s');
        //     Log::info($c_a['haber'] ? 'Haber: '.$c_a['haber']. ' Fecha: '.$fecha.' saldo: '.$c_a['saldo'] : 'Debe '.$c_a['debe'].' Fecha: '.$fecha.' saldo: '.$c_a['saldo']);
        // }
    }

    function setSalesCreatedAt() {
        $news = [];
        foreach ($this->current_acounts as $current_acount) {
            if (!is_null($current_acount['debe'])) {
                $current_acount['created_at'] = $this->getSaleCreatedAt($current_acount);
            }
            $news[] = $current_acount;
        }
        $this->current_acounts = $news;
    }

    function compareCurrentAcountsFromSalesWithExel() {
        $_current_acounts = CurrentAcount::where('client_id', $this->client_id)
                                        ->whereNotNull('sale_id')
                                        ->orderBy('created_at', 'ASC')
                                        ->get();
        foreach ($this->current_acounts as $current_acount) {
            if (!is_null($current_acount['debe'])) {
                foreach ($_current_acounts as $c_a) {
                    if ($this->getNumSale($current_acount) == $c_a->sale->num_sale && $this->getPage($current_acount) == $c_a->page) {
                        if ($current_acount['debe'] != $c_a->debe) {
                            $message = 'El '.$c_a->detalle.' del '.$c_a['created_at']->format('d/m/Y').' es de '.$c_a->debe.' y en el exel figura por '.$current_acount['debe'];
                            $this->messages[] = $message;
                        } 
                        break;
                    }
                }
            }
        }
        foreach ($_current_acounts as $c_a) {
            $finded = false;
            foreach ($this->current_acounts as $current_acount) {
                if ($this->getNumSale($current_acount) == $c_a->sale->num_sale && $this->getPage($current_acount) == $c_a->page) {
                    $finded = true;
                }
            }
            if (!$finded) {
                $message = 'El '.$c_a->detalle.' del '.$c_a['created_at']->format('d/m/Y').' no fue ingresado en el Excel';
                $this->messages[] = $message;
            }
            $finded = false;
        }
    }

    function setSameDate($current_acounts) {
        if (count($current_acounts) >= 1) {
            // Log::info('Llegaron a setSameDate:');
            // foreach ($current_acounts as $current_acount) {
            //     Log::info($current_acount['created_at']);
            // }
            $news_2 = [];
            foreach ($current_acounts as $index => $current_acount) {
                if (!is_null($current_acount['haber'])) {
                    if ($index >= 1) {
                        // Log::info('Seteando la fecha de '.$current_acount['created_at'].' con la de '.$current_acounts[$index-1]['created_at']);
                        $date = $current_acounts[$index-1]['created_at'];
                        $date->addHour();
                        $current_acount['created_at'] = $date;
                        // Log::info('Quedo asi: '.$current_acount['created_at']);
                    }
                }
                $news_2[] = $current_acount;
            }
            foreach ($news_2 as $current_acount) {
                foreach ($this->current_acounts as $index => $ct) {
                    if ($ct['id'] == $current_acount['id']) {
                        $this->current_acounts[$index] = $current_acount;
                    }
                }
            }
            // Log::info('terminaron asi:');
            // foreach ($news_2 as $current_acount) {
            //     Log::info($current_acount['created_at']);
            // }
        }
    }

    function getSaleCreatedAt($current_acount) {
        $sale = Sale::where('num_sale', $this->getNumSale($current_acount))
                        ->where('user_id', UserHelper::userId())
                        ->first();
        if (count($sale->articles) == 0) {
            CurrentAcountHelper::createCurrentAcount($sale, $current_acount);
        }
        return $sale->created_at;
        // return $current_acount['created_at'];
    }

    function save() {
        if (str_contains($row[1], 'N.C')) {
            CurrentAcountHelper::notaCredito($haber, 'N.C', $this->client_id);
        } else {
            CurrentAcountHelper::pagoFromClient($haber, $this->client_id, false, $new_current_acount['created_at']);
        }
    }

    function getCreatedAt($date) {
        $created_at = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
        return Carbon::createFromFormat('Y-m-d', $created_at)->startOfDay()->addHour();
        // if (str_contains($detalle, 'N.C')) {
        //     return Carbon::createFromFormat('Y-m-d', $created_at)->startOfDay();
        // } else {
        //     return Carbon::createFromFormat('Y-m-d', $created_at)->endOfDay();
        // }
    }
}
