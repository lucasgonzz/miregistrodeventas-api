<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\PdfPrintArticle;
use App\Http\Controllers\Helpers\PdfPrintSale;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Sale;
use App\SaleTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class SaleController extends Controller
{

    function statistics() {
        $user = Auth()->user();
        $first_sale = Sale::where('user_id', $user->id)
                            ->orderBy('id', 'ASC')
                            ->first();
        $last_sale = Sale::where('user_id', Auth()->user()->id)
                            ->orderBy('id', 'DESC')
                            ->first();


        $first_date =new Carbon($first_sale->created_at);
        $first_date->addMonth();
        $first_date->day = 1;
        $first_date = Carbon::create($first_date->year, $first_date->month, $first_date->day);

        $last_date =new Carbon($last_sale->created_at);
        $last_date->subMonth();
        $last_date->endOfMonth();
        $last_date = Carbon::create($last_date->year, $last_date->month, $last_date->day);


        $result = [];
        $result['first']['labels'] = [];
        $result['first']['data'] = [];
        $result['second'] = [];
        $result['second']['labels'] = [];
        $result['second']['data'] = [];

        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

        // dd($meses[$first_date->format('n') - 1]);
        $sale_times = SaleTime::where('user_id', Auth()->user()->id)
                                ->get();
        foreach ($sale_times as $sale_time) {
            ${$sale_time->name} = 0;
            $result['second']['labels'][] = $sale_time->name;
        }
        while ($first_date < $last_date) {
            $mes = $meses[($first_date->format('n')) - 1];
            $label = $mes . ' ' . $first_date->year;
            $sales_of_month = Sale::where('user_id', Auth()->user()->id)
                                    ->whereDate('created_at', '>=', $first_date)
                                    ->whereDate('created_at', '<', $first_date->addMonth())
                                    ->with('articles')
                                    ->get();
            // dd($sales_of_month);
            $total = 0;
            $cant_ventas = 0;
            foreach ($sales_of_month as $sale) {
                // dd($sale->articles);
                foreach ($sale->articles as $article) {
                    $total += $article->pivot->price * $article->pivot->amount;
                }
                $cant_ventas++;

                foreach ($sale_times as $sale_time) {
                    $created_at = new Carbon($sale->created_at);

                    $from = new Carbon(date(substr($sale->created_at, 0, 10) . ' ' . $sale_time->from));
                    // $from = Carbon::create($date->year, $date->month, $date->day . ' ' . $sale_time->from);
                    $to = new Carbon(date(substr($sale->created_at, 0, 10) . ' ' . $sale_time->to));
                    if ($from > $to) {
                        // created_at 20 02
                        // from 19 20
                        // to 20 04
                        $to->addDay();
                        if ($created_at >= $from && $created_at < $to) {
                            ${$sale_time->name}++;
                        } else {
                            $to->subDay();
                            $from->subDay();
                            if ($created_at >= $from && $created_at < $to) {
                                ${$sale_time->name}++;
                            }
                        }
                    } else {
                        if ($created_at >= $from && $created_at < $to) {
                            ${$sale_time->name}++;
                        }
                    }
                }
            }
            // dd($mes);
            // $created_at = Carbon::parse($sale->created_at);
            $label = $label . ', ' . $cant_ventas . ' ventas';
            $result['first']['labels'][] = $label;
            $result['first']['data'][] = (int)$total;
            $cantidades_de_cada_horario = [];
            foreach ($sale_times as $sale_time) {
                $cantidades_de_cada_horario[] = ${$sale_time->name};
                ${$sale_time->name} = 0;
            }
            $result['second']['data'][] = $cantidades_de_cada_horario;
        }
        // dd($result);
        return $result;
    }

    function previusNext($index) {
        $sales = Sale::where('user_id', $this->userId())
                        ->with('articles')
                        ->with('client')
                        ->orderBy('id', 'DESC')
                        ->take($index)
                        ->get();
        return response()->json(['sale' => $sales[count($sales)-1]]);
    }

    function pagarDeuda($sale_id, $debt) {
        $sale = Sale::find($sale_id);
        $deuda_pagada = $sale->debt + $debt;
        $total = SaleHelper::getTotalSale($sale);
        if ($deuda_pagada == $total) {
            $sale->debt = null;
        } else {
            $sale->debt = $deuda_pagada;
        }
        $sale->save();
        $sale = Sale::where('id', $sale->id)
                    ->with('articles')
                    ->with('client')
                    ->with('specialPrice')
                    ->first();
        return response()->json(['sale' => $sale], 200);
    }

    function saleClient($client_id) {
        $sales = Sale::where('user_id', $this->userId())
                        ->where('client_id', $client_id)
                        ->with('articles')
                        ->with('client')
                        ->orderby('id', 'DESC')
                        ->get();
        return response()->json(['sales' => $sales], 200);
    }

    function getById($id) {
        return Sale::where('id', $id)
                        ->first();
    }

    function index() {
        $sales = Sale::where('user_id', $this->userId())
                        ->where('created_at', '>=', Carbon::today())
                        ->with('client')
                        // ->with('buyer')
                        ->with('articles')
                        ->with('impressions')
                        ->with('specialPrice')
                        ->with('commissions')
                        ->with('discounts')
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return response()->json(['sales' => $sales], 200);
    }

    function fromSaleTime($sale_time_id, $inverted, $only_one_date = null) {
        $inverted = (bool)$inverted;
        $sale_time = SaleTime::find($sale_time_id);
        if (is_null($only_one_date)) {
            $today = Carbon::today();
            $from = new Carbon(date(substr($today, 0, 10) . ' ' . $sale_time->from));
            $to = new Carbon(date(substr($today, 0, 10) . ' ' . $sale_time->to));
            if ($from > $to) {
                if ($inverted) {
                    $to->addDays(1);
                } else {
                    $from->subDays(1);
                }
            }
        } else {
            $from = date($only_one_date . ' ' . $sale_time->from);
            $to = date($only_one_date . ' ' . $sale_time->to);

            if ($from > $to) {
                // dd('asd');
                if ($inverted) {
                    $to = Carbon::parse($to)->addDays(1);
                } else {
                    $from = Carbon::parse($from)->subDays(1);
                }
            }
        }
        // dd($from);
        return Sale::where('user_id', $this->userId())
                        ->where('created_at', '>=', $from)
                        ->where('created_at', '<', $to)
                        ->with('articles')
                        ->with('impressions')
                        ->with('specialPrice')
                        ->orderBy('id', 'DESC')
                        ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | PreviusNext
    |--------------------------------------------------------------------------
    |
    |   * El parametro index indica el numero de dias a retroceder
    |   * Direction indica si se esta subiendo o bajando, se usa en el caso
    |   de que no haya ventas en tal fecha, si se esta bajando continua bajando
    |   y viceversa
    |   * only_one_date indica si se esta retrocediendo desde una fecha en especifico
    |   Si es nulo es porque se esta retrocediendo desde el principio
    |   Si no es nulo se empieza a retroceder desde la fecha que llega en esa variable
    |   
    */

    function previusDays($index) {
        if ($index == 0) {
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();
        } else {
            $start = Carbon::now()->subWeeks($index)->startOfWeek();
            $end = Carbon::now()->subWeeks($index)->endOfWeek();
        }
        $result = [];
        $index = 0;
        while ($start < $end) {
            // dd($start);
            $start_date = $start->format('Y-m-d H:i:s');
            $end_date = $start->addDay()->format('Y-m-d H:i:s');
            $sales = Sale::where('user_id', $this->userId())
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->get();
            $result[$index]['date'] = $start_date;
            $result[$index]['sales'] = $sales;
            $index++;
        }
        return response()->json(['days_previus_sales' => $result], 200);
    }
    
    function daysPreviusSales($index, $retroceder, $fecha_limite = null) {
        $agent = new Agent();

        if ($agent->isMobile()) {
            $ventas_a_mostrar = 5;
        } else {
            $ventas_a_mostrar = 10;
        }
        
        $ventas_a_mostrar = 5;

        $carbon = Carbon::now('America/Argentina/Buenos_Aires');
        $retroceder = (bool)$retroceder;
        if ($retroceder) {
            $fecha_limite_menor = $carbon->subDays($ventas_a_mostrar * $index)->format('Y-m-d');
            if ($index > 1) {
                $fecha_limite_mayor = Carbon::parse($fecha_limite)->format('Y-m-d');
            } else {
                $fecha_limite_mayor = Carbon::now('America/Argentina/Buenos_Aires')->format('Y-m-d');
            }
        } else {
            $carbon = Carbon::parse($fecha_limite);
            $fecha_limite_menor = $carbon->addDays(1)->format('Y-m-d');
            if ($index > 1) {
                $fecha_limite_mayor = $carbon->addDays($ventas_a_mostrar)->format('Y-m-d');
            } else {
                $fecha_limite_mayor = Carbon::now('America/Argentina/Buenos_Aires')->format('Y-m-d');
            }
        }

        $sales_result = [];
        $a=0;
        $dias_a_restar = 0;
        $dias_a_sumar = 0;
        $first_sale = Sale::where('user_id', $this->userId())
                            ->orderby('id', 'ASC')
                            ->first();

        if ($first_sale === null) {
            return $sales_result;
        } else {

            $first_date = Carbon::parse($first_sale->created_at)->format('Y-m-d');
            // dd($first_date);
            while (count($sales_result) < $ventas_a_mostrar && $fecha_limite_menor >= $first_date) {
                // echo "Fecha limite menor: ".$fecha_limite_menor." ";
                $sales = Sale::where('user_id', $this->userId())
                                ->where('created_at', '>', $fecha_limite_menor)
                                ->where('created_at', '<', $fecha_limite_mayor)
                                ->select('id' ,'created_at')
                                ->orderBy('id', 'ASC')
                                ->get();
                $index_ = -1;
                $last_date = '';
                $sales_result = [];
                foreach ($sales as $sale) {
                    $current_date = Carbon::parse($sale->created_at)->format('Y-m-d');
                    if ($current_date != $last_date) {
                        $index_++;
                        $sales_result[$index_][] = $sale;
                    } else {
                        $sales_result[$index_][] = $sale;
                    }
                    $last_date = $current_date;
                }
                if (count($sales_result) < $ventas_a_mostrar) {
                    if ($retroceder) {
                        $dias_a_restar += $ventas_a_mostrar - count($sales_result);
                        $carbon = Carbon::now('America/Argentina/Buenos_Aires');
                        $fecha_limite_menor = $carbon->subDays($ventas_a_mostrar*$index + $dias_a_restar)->format('Y-m-d');
                    } else {
                        $dias_a_sumar += $ventas_a_mostrar - count($sales_result);
                        $carbon = Carbon::parse($fecha_limite);
                        $fecha_limite_mayor = $carbon->addDays($ventas_a_mostrar + $dias_a_sumar)->format('Y-m-d');
                    }
                    $a++;
                }
            }
            return $sales_result;
        }
    }

    /* ----------------------------------------------------------------------------------------
        Fechas
     ---------------------------------------------------------------------------------------- */

    function onlyOneDate($date) {
        $user = Auth()->user();
        $sales = Sale::where('user_id', $this->userId())
                ->whereDate('created_at', $date)
                ->with('articles')
                ->with('impressions')
                ->with('client')
                ->with('specialPrice')
                ->with('commissions')
                ->with('discounts')
                ->orderBy('created_at', 'DESC')
                ->get();
        return response()->json(['sales' => $sales], 200);
    }

    function fromDate($from, $to, $last_day_inclusive) {
        $user = Auth()->user();
        $last_day_inclusive = (bool)$last_day_inclusive;
        if ($last_day_inclusive) {
            $to = new Carbon($to);
            $to->addDay();
        }
        $sales = Sale::where('user_id', $this->userId())
                ->whereBetween('created_at', [$from, $to])
                ->with('articles')
                ->with('impressions')
                ->with('client')
                ->with('specialPrice')
                ->with('commissions')
                ->with('discounts')
                ->orderBy('created_at', 'DESC')
                ->get();          
        return response()->json(['sales' => $sales], 200);
    }

    /* ----------------------------------------------------------------------------------------
        Basicos
     ---------------------------------------------------------------------------------------- */
    function deleteSales($sales_id) {
        foreach (explode('-', $sales_id) as $sale_id) {
            $sale = Sale::find($sale_id);
            if (auth()->user()->hasRole('provider')) {
                $current_acount = new CurrentAcountController();
                $current_acount->delete($sale);
                $commission = new CommissionController();
                $commission->delete($sale);
            }
            foreach ($sale->articles as $article) {
                if (!is_null($article->stock)) {
                    $article->stock += $article->pivot->amount;
                }
                $article->save();
            }
            $sale->delete();
        }
    }

    function update(Request $request, $id) {
        $user = Auth()->user();
        $sale = Sale::where('id', $id)
                        ->with('articles')
                        ->first();
        SaleHelper::detachArticles($sale);
        SaleHelper::attachArticles($sale, $request->articles);
        $with_card = (bool)$request->with_card;
        if ($with_card) {
            $sale->percentage_card = $user->percentage_card;
        } else {
            $sale->percentage_card = null;
        }
        $sale->save();
        $sale = Sale::where('id', $sale->id)
                        ->with('client')
                        // ->with('buyer')
                        ->with('impressions')
                        ->with('articles')
                        ->with('commissions')
                        ->with('discounts')
                        ->first();
        // Se eliminan las cuentas corrientes y se actualizan los saldos se las siguientes
        $current_acount = new CurrentAcountController();
        $current_acount->delete($sale);

        // Se eliminan las comisiones y se actualizan los saldos se las siguientes
        $commission = new CommissionController();
        $commission->delete($sale);

        $helper = new SaleHelper_Commissioners($sale, $sale->discounts);
        $helper->attachCommissionsAndCurrentAcounts();
        return response()->json(['sale' => $sale], 200);
    }

    function store(Request $request) {
        $with_card = (bool)$request->with_card;
        $special_price_id = null;
        $client_id = $request->client_id;
        if ($client_id == -1) {
            $client_id == null;
        }
        if ($request->special_price_id != 0) {
            $special_price_id = $request->special_price_id;
        }
        $user = Auth()->user();
        $num_sale = SaleHelper::numSale($this->userId());
        $percentage_card = $with_card ? $user->percentage_card : null;
        $sale = Sale::create([
            'user_id' => $this->userId(),
            'num_sale' => $num_sale,
            'debt' => $request->debt,
            'percentage_card' => $percentage_card,
            'client_id' => $client_id,
            'special_price_id' => $special_price_id,
            'sale_type_id' => !is_null($request->sale_type) ? $request->sale_type : null,
        ]);
        SaleHelper::attachArticles($sale, $request->articles);
        if (Auth()->user()->hasRole('provider')) {
            $discounts = DiscountHelper::getDiscountsFromDiscountsId($request->discounts);
            SaleHelper::attachDiscounts($sale, $discounts);
            $helper = new SaleHelper_Commissioners($sale, $discounts);
            $helper->attachCommissionsAndCurrentAcounts();
        }
        $sale = Sale::where('id', $sale->id)
                        ->with('client')
                        ->with('specialPrice')
                        ->with('articles')
                        ->with('impressions')
                        ->with('discounts')
                        ->with('commissions')
                        ->first();
        return response()->json(['sale' => $sale], 201);
    }

    function pdf($sales_id, $company_name, $articles_cost, $articles_subtotal_cost, $articles_total_price, 
                            $articles_total_cost, $borders) {
        $sales_id = explode('-', $sales_id);
        $pdf = new PdfPrintSale(
                                    $sales_id, 
                                    (bool)$company_name, 
                                    (bool)$articles_cost, 
                                    (bool)$articles_subtotal_cost, 
                                    (bool)$articles_total_price, 
                                    (bool)$articles_total_cost, 
                                    (bool)$borders
                                );
        $pdf->printSales();
        // $print_article = new PdfPrintArticle();
        
    }
    // function previusNext($index, $direction, $only_one_date = null) {
    //     $user = Auth()->user();
    //     if (is_null($only_one_date)) {
    //         $carbon = Carbon::now('America/Argentina/Buenos_Aires');
    //         $date = $carbon->subDays($index);
    //     } else {
    //         $carbon = Carbon::create($only_one_date);
    //         if ($direction == 'previus') {
    //             $date = $carbon->subDays($index);
    //         } else {
    //             $date = $carbon->addDays($index);
    //         }
    //     }
    //     $sales = [];

    //     // Se obtine la fecha de la primer compra para saber cuando dejar de buscar
    //     $limit_sale = Sale::where('user_id', $this->userId())
    //                         ->orderBy('id', 'ASC')
    //                         ->first();
    //     $limit_date = $limit_sale->created_at;

    //     while (count($sales) == 0 && $date >= $limit_date && $date <= date('Y-m-d')) {
    //         if ($user->hasRole('provider')) {
    //             $sales = Sale::where('user_id', $this->userId())
    //                                 ->whereDate('created_at', $date)
    //                                 ->orderBy('id', 'DESC')
    //                                 ->with('client')
    //                                 ->with('articles')
    //                                 ->orderBy('created_at', 'DESC')
    //                                 ->get();
    //         } else {
    //             $sales = Sale::where('user_id', $this->userId())
    //                                 ->whereDate('created_at', $date)
    //                                 ->orderBy('id', 'DESC')
    //                                 ->with('articles')
    //                                 ->orderBy('created_at', 'DESC')
    //                                 ->get();
    //         }
    //         if (count($sales) == 0) {
    //             // echo "No tenia en: " . $index;
    //             if ($index != 0) {
    //                 if (is_null($only_one_date)) {
    //                     if ($direction == 'previus') {
    //                         $index++;
    //                     } else {
    //                         $index--;
    //                     }
    //                 } else {
    //                     if ($direction == 'previus') {
    //                         $index--;
    //                     } else {
    //                         $index++;
    //                     }
    //                 }
    //             }
    //             if (is_null($only_one_date)) {
    //                 $carbon = Carbon::now('America/Argentina/Buenos_Aires');
    //             } else {
    //                 $carbon = Carbon::create($only_one_date);
    //             }
    //             if ($index == 0) {
    //                 $date = date('Y-m-d');
    //             } else {
    //                 if ($direction == 'previus') {
    //                     $date = $carbon->subDays($index);
    //                 } else {
    //                     $date = $carbon->addDays($index);
    //                 }
    //             }
    //         }
    //     }
    //     return [
    //         'index' => $index,
    //         'sales' => $sales
    //     ];
    // }
    
}
