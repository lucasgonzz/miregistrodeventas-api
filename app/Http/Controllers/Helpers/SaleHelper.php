<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Client;
use App\Commissioner;
use App\Discount;
use App\Http\Controllers\AfipWsController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\CurrentAcountAndCommissionHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use App\Http\Controllers\SaleController;
use App\Sale;
use App\SaleType;
use App\Service;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SaleHelper extends Controller {

    // Obtiene la ultima venta para retornar el num_sale
    static function numSale($user_id) {
        $last_sale = Sale::where('user_id', $user_id)
                            ->orderby('created_at','DESC')
                            ->first();
        if ($last_sale === null) {
            return 1;
        } else {
            $last_num_sale = $last_sale->num_sale;
            $last_num_sale++;
            return $last_num_sale;
        }
    }

    static function getFullModel($id) {
        $sale = Sale::where('id', $id)
                    ->withAll()
                    ->first();
        return $sale;
    }

    static function getEmployeeId() {
        $user = Auth()->user();
        if (!is_null($user->owner_id)) {
            return $user->id;
        }
        return null;
    }

    static function saveAfipTicket($sale, $save_afip_ticket) {
        if ($save_afip_ticket) {
            Log::info('guardando afip ticket');
            $ct = new AfipWsController();
            $ct->init($sale->id);
        } else {
            Log::info('No se guardo, vino esto');
            Log::info($save_afip_ticket);
        }
    }

    static function getPercentageCard($request) {
        return (bool)$request->with_card ? Auth()->user()->percentage_card : null;
    }

    static function getSpecialPriceId($request) {
            return $request->special_price_id != 0 ? $request->special_price_id : null;
    }

    static function getSaleType($request) {
        return !is_null($request->sale_type) ? $request->sale_type : null;
    }

    static function getSelectedAddress($request) {
        return !is_null($request->selected_address) ? $request->selected_address['id'] : null;
    }

    static function getNumSaleFromSaleId($sale_id) {
        $sale = Sale::where('id', $sale_id)
                    ->select('num_sale')
                    ->first();
        if ($sale) {
            return $sale->num_sale;
        }
        return null;
    }

    static function checkNotaCredito($sale, $request) {
        if ($request->save_nota_credito) {
            $haber = 0;
            $total_article = 0;
            $articles = [];
            foreach ($request->items as $item) {
                if (isset($item['is_article']) && $item['returned_amount'] > 0) {
                    $haber += $item['price_vender'] * $item['returned_amount'];
                    $articles[] = $item;
                }
            }
            if (count($sale->discounts) >= 1) {
                foreach ($sale->discounts as $discount) {
                    $haber -= (float)$discount->pivot->percentage * $haber / 100;
                }
            }
            CurrentAcountHelper::notaCredito($haber, $request->nota_credito_description, 'client', $request->client_id, $sale->id, $articles);
            CurrentAcountHelper::checkSaldos('client', $request->client_id);
        }
    }

    static function attachDiscounts($sale, $discounts_id) {
        $sale->discounts()->detach();
        $discounts = DiscountHelper::getDiscountsFromDiscountsId($discounts_id);
        foreach ($discounts as $discount) {
            $sale->discounts()->attach($discount['id'], [
                'percentage' => $discount['percentage']
            ]);
        }
    }

    static function attachSurchages($sale, $surchages_id) {
        $sale->surchages()->detach();
        $surchages = GeneralHelper::getModelsFromId('Surchage', $surchages_id);
        foreach ($surchages as $surchage) {
            $sale->surchages()->attach($surchage['id'], [
                'percentage' => $surchage['percentage']
            ]);
        }
    }

    static function attachCurrentAcountsAndCommissions($sale, $client_id, $discounts_id, $surchages_id) {
        if ($client_id && $sale->save_current_acount) {
            $discounts = GeneralHelper::getModelsFromId('Discount', $discounts_id);
            $surchages = GeneralHelper::getModelsFromId('Surchage', $surchages_id);
            $helper = new CurrentAcountAndCommissionHelper($sale, $discounts, $surchages, false);
            $helper->attachCommissionsAndCurrentAcounts();
        }
    }

    static function getCantPag($sale) {
        $pag = 1;
        $count = 0;
        foreach ($sale->articles as $article) {
            $count++;
            if ($count > 30) {
                $pag++;
                $count = 0;
            }
        }
        return $pag;
    }

    static function getArticleSalePrice($sale, $article) {
        $price = (float)$article['price_vender'];
        if (!is_null($sale->special_price_id)) {
            foreach ($article['special_prices'] as $special_price) {
                if ($special_price['id'] == $sale->special_price_id) {
                    $price = (float)$special_price['pivot']['price'];
                }
            }
        }
        return $price;
    }

    static function attachArticles($sale, $articles, $dolar_blue = null) {
        foreach ($articles as $article) {
            if (isset($article['is_article'])) {
                $sale->articles()->attach($article['id'], [
                                                            'amount'            => (float)$article['amount'],
                                                            'cost'              => Self::getCost($article),
                                                            'price'             => $article['price_vender'],
                                                            'returned_amount'   => Self::getReturnedAmount($article),
                                                            'delivered_amount'   => Self::getDeliveredAmount($article),
                                                            'discount'          => Self::getDiscount($article),
                                                            // 'with_dolar'        => Self::getDolar($article, $dolar_blue),
                                                            'created_at'        => Carbon::now(),
                                                        ]);
                ArticleHelper::discountStock($article['id'], $article['amount']);
            }
        }
    }

    static function updateItemsPrices($sale, $items) {
        foreach ($items as $item) {
            if (isset($item['is_article']) && $item['price_vender'] != '') {
                $sale->articles()->updateExistingPivot($item['id'], [
                                                        'price' => $item['price_vender'],
                                                    ]);
            } else if (isset($item['is_service']) && $item['price_vender'] != '') {
                $service = Service::find($item['id']);
                $service->price = $item['price_vender'];
                $service->save();
                $sale->services()->updateExistingPivot($item['id'], [
                                                        'price' => $item['price_vender'],
                                                    ]);
            }
        }
    }

    static function attachCombos($sale, $combos) {
        foreach ($combos as $combo) {
            if (isset($combo['is_combo'])) {
                $sale->combos()->attach($combo['id'], [
                                                            'amount' => (float)$combo['amount'],
                                                            'price' => $combo['price'],
                                                            'created_at' => Carbon::now(),
                                                        ]);
            }
        }
    }

    static function attachServices($sale, $services) {
        foreach ($services as $service) {
            if (isset($service['is_service'])) {
                $sale->services()->attach($service['id'], [
                    'price' => $service['price_vender'],
                    'amount' => $service['amount'],
                    'discount' => Self::getDiscount($service),
                ]);
            }
        }
    }

    static function updateCurrentAcountsAndCommissions($sale, $only_commissions = false) {
        // Se eliminan las cuentas corrientes y se actualizan los saldos se las siguientes
        if (!$only_commissions) {
            $current_acount_ct = new CurrentAcountController();
            $current_acount_ct->deleteFromSale($sale);
        }

        // Se eliminan las comisiones y se actualizan los saldos se las siguientes
        $commission_ct = new CommissionController();
        $commission_ct->deleteFromSale($sale);

        $helper = new CurrentAcountAndCommissionHelper($sale, $sale->discounts, $sale->surchages, $only_commissions);
        $helper->attachCommissionsAndCurrentAcounts();

        CurrentAcountHelper::checkSaldos('client', $sale->client_id);

        // $client_controller = new ClientController();
        // $current_acount_ct->checkSaldos($sale->client_id);
    }

    static function checkCommissions($id) {
        $sale = Sale::find($id);
        // Self::updateCurrentAcountsAndCommissions($sale, false);
        Self::updateCurrentAcountsAndCommissions($sale, true);
    }

    static function getDiscount($item) {
        if (isset($item['discount'])) {
            return $item['discount'];
        }
        return null;
    }

    static function getReturnedAmount($item) {
        if (isset($item['returned_amount'])) {
            return $item['returned_amount'];
        }
        return null;
    }

    static function getDeliveredAmount($item) {
        if (isset($item['delivered_amount'])) {
            return $item['delivered_amount'];
        }
        return null;
    }

    static function getCost($item) {
        if (isset($item['cost'])) {
            return $item['cost'];
        }
        return null;
    }

    static function getDolar($article, $dolar_blue) {
        if (isset($article['with_dolar']) && $article['with_dolar']) {
            return $dolar_blue;
        }
        return null;
    }

    static function attachArticlesFromOrder($sale, $articles) {
        foreach ($articles as $article) {
            $sale->articles()->attach($article->id, [
                                            'amount' => $article->pivot->amount,
                                            'cost' => isset($article->pivot->cost)
                                                        ? $article->pivot->cost
                                                        : null,
                                            'price' => $article->pivot->price,
                                        ]);
            
        }
    }

    static function detachItems($sale) {
        foreach ($sale->articles as $article) {
            if (!is_null($article->stock)) {
                $stock = 0;
                $stock = (int)$article->pivot->amount;
                $article->stock += $stock;
                $article->save();
            }
        }
        $sale->articles()->detach();
        $sale->combos()->detach();
        $sale->services()->detach();
    }

    static function getTotalSale($sale, $with_discount = true, $with_surchages = true) {
        $total = 0;
        foreach ($sale->articles as $article) {
            $total += Self::getTotalItem($article);
        }
        foreach ($sale->combos as $combo) {
            $total += Self::getTotalItem($combo);
        }
        foreach ($sale->services as $service) {
            $total += Self::getTotalItem($service);
        }
        if ($with_discount) {
            foreach ($sale->discounts as $discount) {
                $total -= $total * Numbers::percentage($discount->pivot->percentage); 
            }
        }
        if ($with_surchages) {
            foreach ($sale->surchages as $surchage) {
                $total += $total * Numbers::percentage($surchage->pivot->percentage); 
            }
        }
        if (!is_null($sale->percentage_card)) {
            $total += ($total * Numbers::percentage($sale->percentage_card));
        }
        return $total;
    }

    static function getTotalItem($item) {
        $total = $item->pivot->price * $item->pivot->amount;
        if (!is_null($item->pivot->discount)) {
            $total -= $total * ($item->pivot->discount / 100);
        }
        return $total;
    }

    static function getTotalSaleFromArticles($sale, $articles) {
        $total = 0;
        foreach ($articles as $article) {
            if (!is_null($sale->percentage_card)) {
                $total += ($article->pivot->price * Numbers::percentage($sale->percentage_card)) * $article->pivot->amount;
            } else {
                $total += $article->pivot->price * $article->pivot->amount;
            }
        }
        return $total;
    }

    static function getTotalCostSale($sale) {
        $total = 0;
        foreach ($sale->articles as $article) {
            if (!is_null($article->pivot->cost)) {
                $total += $article->pivot->cost * $article->pivot->amount;
            }
        }
        return $total;
    }

    static function isSaleType($sale_type_name, $sale) {
        $sale_type = SaleType::where('user_id', UserHelper::userId())
                                    ->where('name', $sale_type_name)
                                    ->first();
        if (!is_null($sale_type) && $sale->sale_type_id == $sale_type->id) {
            return true;
        } 
        return false;
    }

    static function getPrecioConDescuento($sale) {
        // $discount = DiscountHelper::getTotalDiscountsPercentage($sale->discounts, true);
        $total = Self::getTotalSale($sale);
        foreach ($sale->discounts as $discount) {
            $total -= $total * Numbers::percentage($discount->pivot->percentage); 
        }
        return $total;
        // return Self::getTotalSale($sale) - (Self::getTotalSale($sale) * Numbers::percentage($discount));
    }

    static function getPrecioConDescuentoFromArticles($sale, $articles) {
        $discount = DiscountHelper::getTotalDiscountsPercentage($sale->discounts, true);
        $total = 0;
        foreach ($articles as $article) {
            if (!is_null($sale->percentage_card)) {
                $total += ($article->pivot->price * Numbers::percentage($sale->percentage_card)) * $article->pivot->amount;
            } else {
                $total += $article->pivot->price * $article->pivot->amount;
            }
        }
        return $total - ($total * Numbers::percentage($discount));
    }

    static function getTotalWithDiscountsAndSurchages($sale, $total) {
        foreach ($sale->discounts as $discount) {
            $total -= $total * Numbers::percentage($discount->pivot->percentage);
        }
        foreach ($sale->surchages as $surchage) {
            $total += $total * Numbers::percentage($surchage->pivot->percentage);
        }
        if (!is_null($sale->order) && !is_null($sale->order->cupon)) {
            if (!is_null($sale->order->cupon->percentage)) {
                $total -= $total * $sale->order->cupon->percentage / 100;
            } else if (!is_null($sale->order->cupon->amount)) {
                $total -= $sale->order->cupon->amount;
            }
        }
        return $total;
    }

    static function getTotalMenosDescuentos($sale, $total) {
        foreach ($sale->discounts as $discount) {
            $total -= $total * Numbers::percentage($discount->pivot->percentage);
        }
        return $total;
    }

}

