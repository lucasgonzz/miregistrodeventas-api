<?php

namespace App\Http\Controllers;

use App\Address;
use App\Article;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\PdfPrintArticle;
use App\Http\Controllers\Helpers\PdfPrintSale;
use App\Http\Controllers\Helpers\Pdf\SaleTicketPdf;
use App\Http\Controllers\Helpers\Pdf\Sale\Index as SalePdf;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Sale;
use App\SaleTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class SaleController extends Controller
{

    function index() {
        $sales = Sale::where('user_id', $this->userId())
                        ->where('created_at', '>=', Carbon::today())
                        ->withAll()
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return response()->json(['sales' => $sales], 200);
    }

    function previusNext($index) {
        $sales = Sale::where('user_id', $this->userId())
                        ->withAll()
                        ->orderBy('id', 'DESC')
                        ->take($index)
                        ->get();
        if (count($sales) >= 1) {
            return response()->json(['sale' => $sales[count($sales)-1]]);
        }
        return response()->json(['sale' => null]);
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
            $start_date = $start->format('Y-m-d H:i:s');
            $end_date = $start->addDay()->format('Y-m-d H:i:s');
            $sales = Sale::where('user_id', $this->userId())
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->get();
            $result[$index]['date'] = $start_date;
            $result[$index]['sales'] = $sales;
            $index++;
        }
        return response()->json(['days' => $result], 200);
    }

    /* ----------------------------------------------------------------------------------------
        Fechas
     ---------------------------------------------------------------------------------------- */

    function fromDate($date) {
        $sales = Sale::where('user_id', $this->userId())
                        ->whereDate('created_at', $date)
                        ->withAll()
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return response()->json(['sales' => $sales], 200);
    }

    function betweenDate($from, $to, $last_day_inclusive) {
        $user = Auth()->user();
        $last_day_inclusive = (bool)$last_day_inclusive;
        if ($last_day_inclusive) {
            $to = new Carbon($to);
            $to->addDay();
        }
        $sales = Sale::where('user_id', $this->userId())
                ->whereBetween('created_at', [$from, $to])
                ->withAll()
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
            if ($sale->client_id) {
                $current_acount = new CurrentAcountController();
                $current_acount->deleteFromSale($sale);
                $commission = new CommissionController();
                $commission->delete($sale);
                // CurrentAcountHelper::restartCurrentAcounts($sale->client_id);
            }
            foreach ($sale->articles as $article) {
                ArticleHelper::resetStock($article, $article->pivot->amount);
            }
            $sale->delete();
            // if ($sale->client_id) {
            //     CurrentAcountHelper::createCurrentAcountsFromSales($sale->client_id);
            // }
        }
    }

    function update(Request $request, $id) {
        $user = Auth()->user();
        $sale = Sale::where('id', $id)
                        ->with('articles')
                        ->first();
        SaleHelper::detachItems($sale);
        SaleHelper::attachArticles($sale, $request->items, $request->dolar_blue);
        SaleHelper::attachCombos($sale, $request->items);
        $with_card = (bool)$request->with_card;
        if ($with_card) {
            $sale->percentage_card = $user->percentage_card;
        } else {
            $sale->percentage_card = null;
        }
        $sale->save();
        $sale = Sale::where('id', $sale->id)
                        ->withAll()
                        ->first();
        if ($sale->client_id) {
            SaleHelper::updateCurrentAcountsAndCommissions($sale);
        }
        return response()->json(['sale' => $sale], 200);
    }

    function store(Request $request) {
        $sale = Sale::create([
            'user_id'           => $this->userId(),
            'num_sale'          => SaleHelper::numSale($this->userId()),
            'debt'              => $request->debt,
            'percentage_card'   => SaleHelper::getPercentageCard($request),
            'client_id'         => $request->client_id,
            'special_price_id'  => SaleHelper::getSpecialPriceId($request),
            'sale_type_id'      => SaleHelper::getSaleType($request),
            'address_id'        => SaleHelper::getSelectedAddress($request),
        ]);
        SaleHelper::attachArticles($sale, $request->items, $request->dolar_blue);
        SaleHelper::attachCombos($sale, $request->items);
        if ($request->client_id) {
            $discounts = DiscountHelper::getDiscountsFromDiscountsId($request->discounts);
            SaleHelper::attachDiscounts($sale, $discounts);
            $helper = new SaleHelper_Commissioners($sale, $discounts, false);
            $helper->attachCommissionsAndCurrentAcounts();
        }
        $sale = Sale::where('id', $sale->id)
                    ->withAll()
                    ->first();
        return response()->json(['sale' => $sale], 201);
    }

    function pdf($sales_id, $for_commerce, $afip_ticket = false) {
        $sales_id = explode('-', $sales_id);
        if ($this->isProvider()) {
            foreach ($sales_id as $sale_id) {
                SaleHelper::checkCommissions($sale_id);
            }
        }
        $pdf = new PdfPrintSale($sales_id, (bool)$for_commerce, $afip_ticket);
        // $pdf = new SalePdf($sales_id, (bool)$for_commerce, $afip_ticket);
        $pdf->printSales();
    }

    function ticketPdf($id, $address_id = null) {
        $sale = Sale::find($id);
        if (!is_null($address_id)) {
            $address = Address::find($address_id);
        } else {
            $address = null;
        }
        $pdf = new SaleTicketPdf($sale, $address);
    }
    
}
