<?php

namespace App\Http\Controllers\Helpers\Sale;

use App\Article;
use App\Client;
use App\Commission;
use App\Commissioner;
use App\CurrentAcount;
use App\Discount;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\CommissionHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Sale;
use Illuminate\Support\Facades\Log;
use App\SaleType;

class Commissioners extends Controller {

    function __construct($sale, $discounts, $only_commissions, $index = null) {
        $this->sale = $sale;
        $this->discounts = $discounts;
        $this->client = $sale->client;
        $this->only_commissions = $only_commissions;
        if ($index) {
            $this->index = $index;
        } else {
            $this->index = null;
        }
    }

    // function detachCommissionersAndCurrentAcounts() {
    function detachCommissioners() {
        foreach ($this->sale->commissions as $commission) {
            $commission->delete();
        }
    }

    function attachCommissionsAndCurrentAcounts() {
        $total_articles = count($this->sale->articles);
        $articulos_en_pagina = 0;
        $articulos_en_venta = 0;
        $this->page = 0;
        $this->debe = 0;
        foreach ($this->sale->articles as $article) {
            $articulos_en_venta++;
            $articulos_en_pagina++;
            $this->debe += (float)$article->pivot->price * (int)$article->pivot->amount;
            if ($articulos_en_pagina == 30 || $articulos_en_venta == $total_articles) {
                $this->page++;
                $this->debe_sin_descuentos = $this->debe;
                if ($this->hasSaleDiscounts()) {
                    $this->debe = SaleHelper::getTotalMenosDescuentos($this->sale, $this->debe);
                }
                $this->createCurrentAcount();
                if ($this->isProvider()) {
                    if ($this->isSaleFromSeller()) {
                        $this->commissionForSeller();
                        if ($this->isSellerFromSeller() && $this->isDiscountMenosQue10()) {
                            $this->commissionForSellerOwner();
                        }
                    } else {
                        $this->commissionForPerdidas();
                    }
                    $this->commissionOscarFedePapi();
                }
                $articulos_en_pagina = 0;
                $this->debe = 0;
            }
        }
    }

    function hasSaleDiscounts() {
        return !is_null($this->sale->discounts);
    }

    function createCurrentAcount() {
        if (!$this->only_commissions) {
            $current_acount = CurrentAcount::create([
                'detalle'     => 'Rto '.$this->sale->num_sale.' pag '.$this->page,
                'page'        => $this->page,
                'debe'        => $this->debe,
                'status'      => 'sin_pagar',
                'client_id'   => $this->sale->client_id,
                'seller_id'   => $this->sale->client->seller_id,
                'sale_id'     => $this->sale->id,
                'description' => CurrentAcountHelper::getDescription($this->sale, $this->debe_sin_descuentos),
                'created_at' => $this->getCreatedAt(),
            ]);
            $current_acount->saldo = Numbers::redondear(CurrentAcountHelper::getSaldo($this->sale->client_id, $current_acount) + $this->debe);
            $current_acount->save();
        }
    }

    function isSaleFromSeller() {
        if (!is_null($this->client->seller_id)) {
            return true;
        } 
        return false;
    }

    function commissionForSeller() {
        $seller_commissioner = $this->getSellerCommissioner();
        $commission = Commission::create([
            'commissioner_id' => $seller_commissioner->id,
            'sale_id'         => $this->sale->id,
            'page'            => $this->page,
            'percentage'      => $this->getSellerPercentage(),
            'monto'           => $this->getSellerMonto(),
            'status'          => 'inactive',
            'detalle'         => $this->getDetalle(),
            'is_seller'       => 1,
            'updated_at'      => null,
            'created_at'      => $this->getCreatedAt(),
        ]);
    }

    function getDetalle() {
        $detalle = 'Comision '.$this->client->name.' remito '.$this->sale->num_sale;
        $detalle .= ' pag '.$this->page;
        $detalle .= ' ($'.Numbers::price($this->debe).')';
        return $detalle;
    }

    function commissionForSellerOwner() {
        $seller_seller_commissioner = Commissioner::where('seller_id', $this->client->seller->seller_id)
                                            ->first();
        $commission = Commission::create([
            'commissioner_id' => $seller_seller_commissioner->id,
            'sale_id'         => $this->sale->id,
            'page'            => $this->page,
            'percentage'      => $this->getSellerSellerPercentage(),
            'detalle'         => $this->getDetalle(),
            'monto'           => $this->getSellerSellerMonto(),
            'status'          => 'inactive',
            'detalle'         => $this->getDetalle(),
            'is_seller'       => 0,
            'updated_at'      => null,
            'created_at' => $this->getCreatedAt(),
        ]);
    }

    function getSellerCommissioner() {
        $seller_commissioner = Commissioner::where('seller_id', $this->client->seller_id)
                                            ->first();
        return $seller_commissioner;
    }

    function getSellerPercentage() {
        $discounts_percentage = DiscountHelper::getTotalDiscountsPercentage($this->discounts);
        if (SaleHelper::isSaleType('varios', $this->sale)) {
            if ($discounts_percentage > 10) {
                return 3;
            } else {
                return 5;
            }
        } else {
            if ($discounts_percentage < 20) {
                return 10;
            } else if ($discounts_percentage < 25 || $this->tieneUn10MasUn20()) {
                return 7;
            } else if ($discounts_percentage >= 25) {
                return 5;
            }
        }
    }

    function tieneUn10MasUn20() {
        $has_10 = false;
        $has_20 = false;
        foreach ($this->discounts as $discount) {
            if ($discount->percentage == 10) {
                $has_10 = true;
            }
            if ($discount->percentage == 20) {
                $has_20 = true;
            }
        }
        return $has_10 && $has_20;
    }

    function getSellerSellerPercentage() {
        if (SaleHelper::isSaleType('varios', $this->sale)) {
            return 5;
        } else {
            return 10;
        }
    }

    function getSellerMonto() {
        $commission_seller = $this->debe * Numbers::percentage($this->getSellerPercentage());
        return Numbers::redondear($commission_seller);
    }

    function getSellerSellerMonto() {
        $total_a_restar = $this->debe;
        $total_a_restar -= $this->getSellerMonto();
        $commission_seller_owner = $total_a_restar * Numbers::percentage($this->getSellerSellerPercentage());
        return Numbers::redondear($commission_seller_owner);
    }

    function isSellerFromSeller() {
        if (!is_null($this->client->seller) && !is_null($this->client->seller->seller_id)) {
            return true;
        }
        return false;
    }

    function isDiscountMenosQue10() {
        if (DiscountHelper::getTotalDiscountsPercentage($this->discounts) < 10) {
            return true;
        }
        return false;
    }

    function commissionForPerdidas() {
        if (UserHelper::isOscar()) {
            $perdidas_commissioner = Commissioner::where('user_id', UserHelper::userId())
                                                    ->where('name', 'Perdidas')
                                                    ->first();
            $commission = Commission::create([
                'commissioner_id' => $perdidas_commissioner->id,
                'sale_id'         => $this->sale->id,
                'detalle'         => $this->getDetalle(),
                'saldo'           => CommissionHelper::getCommissionerSaldo($perdidas_commissioner) + $this->getPerdidaMonto(),
                'page'            => $this->page,
                'percentage'      => $this->getPerdidaPercentage(),
                'monto'           => $this->getPerdidaMonto(),
                'updated_at'      => null,
                'created_at' => $this->getCreatedAt(),
            ]);
        }
    }

    function getPerdidaPercentage() {
        $discounts_percentage = DiscountHelper::getTotalDiscountsPercentage($this->discounts);
        if ($discounts_percentage < 30) {
            return 5;
        } else {
            return 2;
        } 
    }

    function getPerdidaMonto() {
        $commission_perdidas = $this->debe * Numbers::percentage($this->getPerdidaPercentage());
        return Numbers::redondear($commission_perdidas);
    }

    function commissionOscarFedePapi() {
        if (UserHelper::isOscar()) {
            $commissioners = Commissioner::where('user_id', UserHelper::userId())
                                        ->where('name', '!=', 'Perdidas')
                                        ->whereNull('seller_id')
                                        ->get();
            foreach ($commissioners as $commissioner) {
                $commission = Commission::create([
                    'commissioner_id' => $commissioner->id,
                    'sale_id'         => $this->sale->id,
                    'saldo'           => CommissionHelper::getCommissionerSaldo($commissioner) + $this->getMontoForOscarFedePapi($commissioner),
                    'detalle'         => $this->getDetalle(),
                    'page'            => $this->page,
                    'percentage'      => $commissioner->percentage,
                    'monto'           => $this->getMontoForOscarFedePapi($commissioner),
                    'updated_at'      => null,
                    'created_at' => $this->getCreatedAt(),
                ]);
            }
        }
    }

    function getMontoForOscarFedePapi($commissioner) {
        $total_a_restar = $this->debe;
        if ($this->isSellerFromSeller() && $this->isDiscountMenosQue10()) {
            $total_a_restar -= $this->getSellerMonto();
        } 
        $commission = $total_a_restar * Numbers::percentage($commissioner->percentage);
        return Numbers::redondear($commission);
    }

    function getCreatedAt() {
        // $created_at = Carbon::now();
        $created_at = $this->sale->created_at;
        if ($this->page > 1) {
            $created_at = $this->sale->created_at->addSeconds($this->page);
        }
        if ($this->index) {
            $created_at->subDays($this->index);
        }
        return $created_at;
    }
}

