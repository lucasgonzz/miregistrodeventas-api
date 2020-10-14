<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Sale;
use App\Client;

class SaleHelper {

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

    static function attachArticles($sale, $articles) {
        foreach ($articles as $article) {
            $price = 0;
            $sale->articles()->attach($article['id'], [
                                                        'amount' => (float)$article['amount'],
                                                        'measurement' => 
                                                                        isset($article['measurement']) 
                                                                        ? $article['measurement'] 
                                                                        : null,
                                                        'cost' => isset($article['cost'])
                                                                    ? (float)$article['cost']
                                                                    : null,
                                                        'price' => (float)$article['price'],
                                                    ]);
            $article_ = Article::find($article['id']);
            if (!is_null($article_->stock)) {
                if ($article_->uncontable == 1) {
                    if ($article['measurement'] != $article['measurement_original']) {
                        $stock_resultante = $article_->stock - ((float)$article['amount'] / 1000);
                    } else {
                        $stock_resultante = $article_->stock - (float)$article['amount'];
                    }
                } else {
                    $stock_resultante = $article_->stock - $article['amount'];
                }
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
                if ($article_->uncontable == 1) {
                    if ($article['measurement'] != $article['measurement_original']) {
                        $stock_resultante = $article_->stock - ((float)$article->pivot->amount / 1000);
                    } else {
                        $stock_resultante = $article_->stock - (float)$article->pivot->amount;
                    }
                } else {
                    $stock_resultante = $article_->stock - $article->pivot->amount;
                }
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
            $stock = 0;
            // $article = Article::find($article_->id);
            if ($article->uncontable == 1) {
                if ($article->pivot->measurement != $article->measurement) {
                    $stock = (float)$article->pivot->amount / 1000;
                } 
            } else {
                $stock = (float)$article->pivot->amount;
            }
            $article->stock += $stock;
            $article->save();
        }

        // foreach ($articles as $article) {
        //     $article_ = Article::find($article['id']);
        //     if (!is_null($article_->stock)) {
        //         if ($article_->uncontable == 1) {
        //             if ($article['measurement'] != $article['measurement_original']) {
        //                 $stock_a_restaurar = ((float)$article['amount'] / 1000);
        //             } else {
        //                 $stock_a_restaurar = (float)$article['amount'];
        //             }
        //         } else {
        //             $stock_a_restaurar = (float)$article['amount'];
        //         }
        //         $article_->stock += $stock_a_restaurar;
        //         $article_->timestamps = false;
        //         $article_->save();
        //     }
        // }
        $sale->articles()->detach();
    }

}

