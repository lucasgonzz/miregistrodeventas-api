<?php

namespace App\Http\Controllers\Helpers\Sale;

use App\Article;
use App\Client;
use App\Commission;
use App\Commissioner;
use App\CurrentAcount;
use App\Discount;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\CommissionHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Sale;
use App\SaleType;

class Commissioners extends Controller {

    function __construct($sale, $discounts) {
        $this->sale = $sale;
        $this->discounts = $discounts;
        $this->client = $sale->client;
    }

    function detachCommissionersAndCurrentAcounts() {
        foreach ($this->sale->commissions as $commission) {
            $commission->delete();
        }
        // $current_acounts = CurrentAcount::where('sale_id', $this->sale->id)->get();
        foreach ($this->sale->current_acounts as $current_acount) {
            $current_acount->delete();
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
            if ($articulos_en_pagina >= 30 || $articulos_en_venta == $total_articles) {
                $this->page++;
                $this->debe_sin_descuentos = $this->debe;
                if ($this->hasSaleDiscounts()) {
                    $this->debe = SaleHelper::getTotalMenosDescuentos($this->sale, $this->debe);
                }
                $this->createCurrentAcount();
                if ($this->isSaleFromSeller()) {
                    $this->commissionForSeller();
                    if ($this->isSellerFromSeller() && $this->isDiscountMenosQue10()) {
                        // Se le da la comision al dueño del vendedor
                        $this->commissionForSellerOwner();
                    }
                } else {
                    $this->commissionForPerdidas();
                }
                // Se les da la comision a Oscar, Fede y al papa
                $this->commissionOscarFedePapi();
                $articulos_en_pagina = 0;
                $articulos_en_pagina++;
                $this->debe = 0;
                // $this->debe += (float)$article->pivot->price * (int)$article->pivot->amount;
            }
        }
    }

    function hasSaleDiscounts() {
        return !is_null($this->sale->discounts);
    }

    function createCurrentAcount() {
        $current_acount = CurrentAcount::create([
            'detalle'     => 'Rto '.$this->sale->num_sale.' pag '.$this->page,
            'page'        => $this->page,
            'debe'        => $this->debe,
            'saldo'       => CurrentAcountHelper::getSaldo($this->sale->client_id) + $this->debe,
            'status'      => 'sin_pagar',
            'client_id'   => $this->sale->client_id,
            'seller_id'   => $this->sale->client->seller_id,
            'sale_id'     => $this->sale->id,
            'description' => CurrentAcountHelper::getDescription($this->sale, $this->debe_sin_descuentos),
        ]);
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
            } else if ($discounts_percentage < 25) {
                return 7;
            } else if ($discounts_percentage >= 25) {
                return 5;
            }
        }
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
        return $commission_seller;
    }

    function getSellerSellerMonto() {
        $total_a_restar = $this->debe;
        $total_a_restar -= $this->getSellerMonto();
        $commission_seller_owner = $total_a_restar * Numbers::percentage($this->getSellerSellerPercentage());
        return $commission_seller_owner;
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
        ]);
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
        return $commission_perdidas;
    }

    function commissionOscarFedePapi() {
        $commissioners = Commissioner::where('user_id', UserHelper::userId())
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
            ]);
            // $this->sale->commissioners()->attach($commissioner->id, [
            //     'percentage' => $commissioner->percentage
            // ]);
        }
    }

    function getMontoForOscarFedePapi($commissioner) {
        $total_a_restar = $this->debe;
        if ($this->isSellerFromSeller() && $this->isDiscountMenosQue10()) {
            $total_a_restar -= $this->getSellerMonto();
        } 
        $commission = $total_a_restar * Numbers::percentage($commissioner->percentage);
        return $commission;
    }
}

