<?php

namespace App\Http\Controllers;

use App\Address;
use App\Article;
use App\CurrentAcount;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\PdfPrintArticle;
use App\Http\Controllers\Helpers\PdfPrintSale;
use App\Http\Controllers\Helpers\Pdf\NewSalePdf;
use App\Http\Controllers\Helpers\Pdf\SaleAfipTicketPdf;
use App\Http\Controllers\Helpers\Pdf\SaleDeliveredArticlesPdf;
use App\Http\Controllers\Helpers\Pdf\SaleTicketPdf;
use App\Http\Controllers\Helpers\Pdf\Sale\Index as SalePdf;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Notifications\CreatedSale;
use App\Sale;
use App\SaleTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class SaleController extends Controller
{

    function index($from_date, $until_date = null) {
        $models = Sale::where('user_id', $this->userId());
        if (!is_null($until_date)) {
            $models = $models->whereDate('created_at', '>=', $from_date)
                            ->whereDate('created_at', '<=', $until_date);
        } else {
            $models = $models->whereDate('created_at', $from_date);
        }
        $models = $models->withAll()
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return response()->json(['models' => $models], 200);
    }

    function show($id) {
        $model = Sale::where('id', $id)
                        ->withAll()
                        ->first();
        return response()->json(['model' => $model], 200);
    }

    function previusNext($index) {
        $sales = Sale::where('user_id', $this->userId())
                        ->withAll()
                        ->orderBy('id', 'DESC')
                        ->take($index)
                        ->get();
        if (count($sales) >= 1) {
            $sale = $sales[count($sales)-1];
            $sale->articles = ArticleHelper::setPrices($sale->articles);
            return response()->json(['sale' => $sale]);
        }
        return response()->json(['sale' => null]);
    }

    function getIndexPreviusNext($id) {
        $sale = Sale::find($id);
        $models = Sale::where('user_id', $this->userId())
                        ->where('created_at', '>=', $sale->created_at)
                        ->pluck('id');
        return response()->json(['index' => count($models)], 200);
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

    function previusDays($index) {
        $days = GeneralHelper::previusDays('App\Sale', $index);
        return response()->json(['days' => $days], 200);
    }

    function saveCurrentAcount($id) {
        $sale = Sale::find($id);
        $sale->save_current_acount = 1;
        $sale->save();
        SaleHelper::attachCurrentAcountsAndCommissions($sale, $sale->client_id, $sale->discounts_id);
        CurrentAcountHelper::checkSaldos($sale->client_id);
        $sale = Sale::where('id', $id)
                        ->withAll()
                        ->first();
        return response()->json(['model' => $sale], 200);
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
                $commission->deleteFromSale($sale);
                CurrentAcountHelper::checkSaldos('client', $sale->client_id);
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
        SaleHelper::attachServices($sale, $request->items);
        SaleHelper::attachDiscounts($sale, $request->discounts_id);
        SaleHelper::checkNotaCredito($sale, $request);
        $with_card = (bool)$request->with_card;
        if ($with_card) {
            $sale->percentage_card = $user->percentage_card;
        } else {
            $sale->percentage_card = null;
        }
        if (!is_null($request->client_id) && $request->client_id != $sale->client_id) {
            $current_acount = CurrentAcount::where('sale_id', $sale->id)->first();
            if (!is_null($current_acount)) {
                $current_acount->delete();
                CurrentAcountHelper::checkSaldos('client', $sale->client_id);
            }
            $sale->client_id = $request->client_id;
        }
        $sale->updated_at = Carbon::now();
        $sale->save();
        $sale = Sale::where('id', $sale->id)
                        ->withAll()
                        ->first();
        if ($sale->client_id) {
            SaleHelper::updateCurrentAcountsAndCommissions($sale);
        }
        return response()->json(['sale' => $sale], 200);
    }

    function updatePrices(Request $request, $id) {
        $model = Sale::find($id);
        SaleHelper::updateArticlesPrices($model, $request->articles);
        if ($model->client_id) {
            SaleHelper::updateCurrentAcountsAndCommissions($model);
        }
        return response()->json(['model' => $this->fullModel('App\Sale', $id)], 200);
    }

    function store(Request $request) {
        $sale = Sale::create([
            'user_id'               => $this->userId(),
            'num_sale'              => SaleHelper::numSale($this->userId()),
            'percentage_card'       => SaleHelper::getPercentageCard($request),
            'client_id'             => $request->client_id,
            'special_price_id'      => SaleHelper::getSpecialPriceId($request),
            'sale_type_id'          => SaleHelper::getSaleType($request),
            'address_id'            => SaleHelper::getSelectedAddress($request),
            'save_current_acount'   => $request->save_current_acount,
            'price_type_id'         => $request->price_type_id,
            'employee_id'           => SaleHelper::getEmployeeId(),
        ]);
        SaleHelper::attachArticles($sale, $request->items, $request->dolar_blue);
        SaleHelper::attachCombos($sale, $request->items);
        SaleHelper::attachServices($sale, $request->items);

        SaleHelper::attachDiscounts($sale, $request->discounts_id);
        SaleHelper::attachCurrentAcountsAndCommissions($sale, $request->client_id, $request->discounts_id);
        SaleHelper::saveAfipTicket($sale, $request->save_afip_ticket);
        $sale = Sale::where('id', $sale->id)
                    ->withAll()
                    ->first();
        $sale->user->notify(new CreatedSale($sale));
        return response()->json(['sale' => $sale], 201);
    }

    function pdf($sales_id, $for_commerce, $afip_ticket = false) {
        $sales_id = explode('-', $sales_id);
        $pdf = new PdfPrintSale($sales_id, (bool)$for_commerce, $afip_ticket);
        $pdf->printSales();
    }

    function pdfAfipTicket($sale_id) {
        $pdf = new SaleAfipTicketPdf($sale_id);
        $pdf->printSales();
    }

    function newPdf($id) {
        $sale = Sale::find($id);
        $pdf = new NewSalePdf($sale);
    }

    function deliveredArticlesPdf($id) {
        $sale = Sale::find($id);
        $pdf = new SaleDeliveredArticlesPdf($sale);
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
