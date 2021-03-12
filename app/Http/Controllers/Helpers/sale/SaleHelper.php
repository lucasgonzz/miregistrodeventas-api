<?php

namespace App\Http\Controllers\Helpers\Sale;

use App\Article;
use App\Client;
use App\Commissioner;
use App\Discount;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use App\Sale;
use App\SaleType;

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

    static function getNumSaleFromSaleId($sale_id) {
        return Sale::where('id', $sale_id)
                    ->select('num_sale')
                    ->first()->num_sale;
    }

    static function attachDiscounts($sale, $discounts) {
        foreach ($discounts as $discount) {
            $sale->discounts()->attach($discount->id, [
                'percentage' => $discount->percentage
            ]);
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

    static function attachArticles($sale, $articles) {
        foreach ($articles as $article) {
            $price = 0;
            $sale->articles()->attach($article['id'], [
                                                        'amount' => (float)$article['amount'],
                                                        'cost' => isset($article['cost'])
                                                                    ? (float)$article['cost']
                                                                    : null,
                                                        'price' => (float)$article['price'],
                                                    ]);
            $article_ = Article::find($article['id']);
            if (!is_null($article_->stock)) {
                $stock_resultante = $article_->stock - $article['amount'];
                if ($stock_resultante > 0) {
                    $article_->stock = $stock_resultante;
                } else {
                    $article_->stock = 0;
                }
                $article_->timestamps = false;
                $article_->save();
            }
        }
    }

    static function attachArticlesFromOrder($order, $articles) {
        foreach ($articles as $article) {
            $price = 0;
            $order->articles()->attach($article->id, [
                                                        'amount' => $article->pivot->amount,
                                                        'cost' => isset($article->pivot->cost)
                                                                    ? $article->pivot->cost
                                                                    : null,
                                                        'price' => $article->pivot->price,
                                                    ]);
            $article_ = Article::find($article->id);
            if (!is_null($article_->stock)) {
                $stock_resultante = $article_->stock - $article->pivot->amount;
                if ($stock_resultante > 0) {
                    $article_->stock = $stock_resultante;
                } else {
                    $article_->stock = 0;
                }
                $article_->timestamps = false;
                $article_->save();
            }
        }
    }

    static function detachArticles($sale) {
        foreach ($sale->articles as $article) {
            if (!is_null($article->stock)) {
                $stock = 0;
                $stock = (int)$article->pivot->amount;
                $article->stock += $stock;
                $article->save();
            }
        }
        $sale->articles()->detach();
    }

    static function getTotalSale($sale, $with_discount = true) {
        $total = 0;
        foreach ($sale->articles as $article) {
            if (!is_null($sale->percentage_card)) {
                $total += ($article->pivot->price * Numbers::percentage($sale->percentage_card)) * $article->pivot->amount;
            } else {
                $total += $article->pivot->price * $article->pivot->amount;
            }
        }
        if ($with_discount) {
            foreach ($sale->discounts as $discount) {
                $total -= $total * Numbers::percentage($discount->pivot->percentage); 
            }
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
        if ($sale->sale_type_id == $sale_type->id) {
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

    static function getTotalMenosDescuentos($sale, $total) {
        foreach ($sale->discounts as $discount) {
            $total -= $total * Numbers::percentage($discount->pivot->percentage);
        }
        return $total;
    }

}

