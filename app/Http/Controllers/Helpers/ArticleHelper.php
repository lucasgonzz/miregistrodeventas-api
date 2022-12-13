<?php

namespace App\Http\Controllers\Helpers;

use App\Advise;
use App\Article;
use App\ArticleDiscount;
use App\Description;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\MessageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use App\Mail\Advise as AdviseMail;
use App\Mail\ArticleAdvise;
use App\PriceType;
use App\SpecialPrice;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ArticleHelper {

    static function setArticlesFinalPrice($company_name = null) {
        if (!is_null($company_name)) {
            // echo ('company_name: '.$company_name);
            $user_id = User::where('company_name', $company_name)
                            ->first()->id;
        } else {
            $user_id = UserHelper::userId();
        }
        $articles = Article::where('user_id', $user_id)
                            ->get();
        $index = 1;
        foreach ($articles as $article) {
            // echo('articulo '.$index.'</br>');
            Self::setFinalPrice($article, $user_id);
            $index++;
        }
    }

    static function setFinalPrice($article, $user_id = null) {
        if (is_null($user_id)) {
            $user = UserHelper::user();
        } else {
            $user = User::find($user_id);
        }
        if (!is_null($article->percentage_gain) || ($article->apply_provider_percentage_gain && !is_null($article->provider) && !is_null($article->provider->percentage_gain))) {
            $article->price = null;
            $article->save();
        }
        if (is_null($article->price) || $article->price == '') {
            $cost = $article->cost;
            if ($article->cost_in_dollars) {
                $cost = $cost * $user->dollar;
            } else if ($article->provider_cost_in_dollars && !is_null($article->provider->dolar)) {
                // Log::info('dolar_provider: '.$article->provider->dolar);
                $cost = $cost * $article->provider->dolar;
                // Log::info('nuevo costo: '.$cost);
            }
            $final_price = $cost;
            if ($article->apply_provider_percentage_gain) {
                if (!is_null($article->provider_price_list)) {
                    // Log::info('sumando provider_price_list de '.$article->provider_price_list->percentage);
                    // Log::info('final_price esta en '.$final_price);
                    $final_price = $cost + ($cost * $article->provider_price_list->percentage / 100);
                    // Log::info('final_price quedo en '.$final_price);
                } else if ((!is_null($article->provider) && $article->provider->percentage_gain)) {
                    // Log::info('sumando provider->percentage_gain de '.$article->provider->percentage_gain);
                    // Log::info('final_price esta en '.$final_price);
                    $final_price = $cost + ($cost * $article->provider->percentage_gain / 100);
                    // Log::info('final_price quedo en '.$final_price);
                } else {
                    // Log::info('no se sumo ningun marguen de ganancia de proveedor');
                    $final_price = $cost;
                    // Log::info('final_price quedo en '.$final_price);
                }
            }
            if (!is_null($article->percentage_gain)) {
                // Log::info('sumando article->percentage_gain de '.$article->percentage_gain);
                // Log::info('final_price esta en '.$final_price);
                $final_price += $final_price * $article->percentage_gain / 100; 
                // if ($final_price > 0) {
                //     $final_price += $final_price * $article->percentage_gain / 100; 
                // } else {
                //     $final_price += $cost * $article->percentage_gain / 100; 
                // }
                // Log::info('final_price quedo en '.$final_price);
            }
        } else {
            $final_price = $article->price;
        }

        if (!$user->configuration->iva_included && Self::hasIva($article)) {
            // Log::info('sumando iva de '.$article->iva->percentage);
            // Log::info('final_price esta en '.$final_price);
            $final_price += $final_price * $article->iva->percentage / 100;
            // Log::info('final_price quedo en '.$final_price);
        }
        if (count($article->discounts) >= 1) {
            foreach ($article->discounts as $discount) {
                $final_price -= $final_price * $discount->percentage / 100;
            }
        }
        $article->final_price = $final_price;

        // echo($article->name.' final_price: '.$article->final_price.' </br>');
        // echo('-------------------------------------------------------------- </br>');
        $article->timestamps = false;
        $article->save();
    }

    static function clearCost($article) {
        $cost = substr($article->cost, 0, strpos($article->cost, '.'));
        $decimals = substr($article->cost, strpos($article->cost, '.')+1);
        if (substr($decimals, 2) == '0000') {
            $decimals = substr($decimals, 0, 2);
        }
        $article->cost = floatval($cost.'.'.$decimals);
    }

    static function getById($articles_ids) {
        $models = [];
        foreach ($articles_ids as $id) {
            $models[] = ArticleHelper::getFullArticle($id);
        }
        return $models;
    }

    static function lastProviderPercentageGain($article) {
        if (!is_null($article->provider) && $article->provider->percentage_gain) {
            return $article->provider->percentage_gain;
        } 
        return null;
        // $last_provider = Self::lastProvider($article);
        // if (!is_null($last_provider) && !is_null($last_provider->percentage_gain)) {
        //     return $last_provider->percentage_gain;
        // }
        // return null;
    }

    static function lastProvider($article) {
        if (count($article->providers) >= 1) {
            $last_provider = $article->providers[count($article->providers)-1];
            if (!is_null($last_provider)) {
                return $last_provider;
            }
        }
        return null;
    }

    static function hasIva($article) {
        return !is_null($article->iva) && $article->iva->percentage != '0' && $article->iva->percentage != 'Exento' && $article->iva->percentage != 'No Gravado'; 
    }

    static function setIva($articles) {
        $ct = new Controller();
        foreach ($articles as $article) {
            $article->iva_id = $ct->getModelBy('ivas', 'id', $article->iva_id, false, 'percentage'); 
        }
        return $articles;
    }

    static function attachProvider($article, $request) {
        if ($request->provider_id != 0) {
            $article->providers()->attach($request->provider_id, [
                                            'amount' => $request->stock,
                                            'cost'   => $request->cost,
                                            // 'price'  => $request->price
                                        ]);
        }
    }

    static function saveProvider($article, $request) {
        if (
            // No tiene provedor y llega uno en request
            (count($article->providers) == 0 && $request->provider_id != 0) ||

            // Tiene provedores, llega provedor en request, y el ultimo proveedor que tiene es distinto del que llego
            (count($article->providers) >= 1 && $request->provider_id != 0 && $article->providers[count($article->providers)-1]->id != $request->provider_id) ||

            // Tiene proveedor, llega el mismo proveedor pero con otro costo
            (count($article->providers) >= 1 && $article->providers[count($article->providers)-1]->id == $request->provider_id && $article->cost != $request->cost) ||

            // Tiene proveedor, llega el mismo proveedor pero con otro stock
            (count($article->providers) >= 1 && $article->providers[count($article->providers)-1]->id == $request->provider_id && $article->stock != $request->stock)
        ) {
            Log::info('entro a guardar proveedor');
            $request_stock = (float)$request->stock;
            if ($request_stock > 0) {
                if (!is_null($article->stock)) {
                    $stock_actual = $article->stock;
                } else {
                    $stock_actual = 0;
                }
                $amount = $request_stock - $stock_actual;
            } else {
                $amount = null;
            }
            $article->providers()->attach($request->provider_id, [
                                    'amount'    => $amount,
                                    'cost'      => $request->cost,
                                    // 'price'     => $request->price,
                                ]);
        }
    }

    static function setDiscount($articles) {
        foreach ($articles as $article) {
            if (count($article->discounts) >= 1) {
                $article->slug = $article->discounts[0]->percentage;
            } else {
                $article->slug = 'no tinee';
            }
            // foreach ($article->discounts as $discount) {
            //     $article->slug .= $discount->percentage.' ';
            // }
        }
        return $articles;
    }

    static function checkAdvises($article) {
        $advises = Advise::where('article_id', $article->id)
                            ->get();
        if ($article->stock >= 1 && count($advises) >= 1) {
            foreach ($advises as $advise) {
                Mail::to($advise->email)->send(new AdviseMail($article));
                $advise->delete();
            }
        }
    }

    static function discountStock($id, $amount) {
        $article = Article::find($id);
        if (!is_null($article->stock)) {
            $stock_resultante = $article->stock - $amount;
            $article->stock = $stock_resultante;
            // if ($stock_resultante > 0) {
            //     $article->stock = $stock_resultante;
            // } else {
            //     $article->stock = 0;
            // }
            $article->timestamps = false;
            $article->save();
        }
    }

    static function resetStock($article, $amount) {
        if (!is_null($article->stock)) {
            $article->stock += $amount;
        }
        $article->timestamps = false;
        $article->save();
    }

    static function getShortName($name, $length) {
        if (strlen($name) > $length) {
            $name = substr($name, 0, $length) . '..';
        }
        return $name;
    }

    static function setSpecialPrices($article, $request) {
        $special_prices = SpecialPrice::where('user_id', UserHelper::userId())->get();
        if ($special_prices) {
            $article->specialPrices()->sync([]);
            foreach ($special_prices as $special_price) {
                if ($request->{$special_price->name} != '') {
                    $article->specialPrices()
                    ->attach(
                        $special_price->id, 
                        ['price' => (double)$request->{$special_price->name}]
                    );
                }
            }
        }
    }

    static function setDeposits($article, $request) {
        $article->deposits()->detach();
        if (isset($request->deposits)) {
            foreach ($request->deposits as $id => $value) {
                if ($value != '') {
                    $article->deposits()->attach($id, [
                                                    'value' => $value
                                                ]);
                }
            }
        }
    }

    static function setTags($article, $tags) {
        $article->tags()->sync([]);
        if (isset($tags)) {
            foreach ($tags as $tag) {
                $article->tags()->attach($tag['id']);
            }
        }
    }

    static function setDiscounts($article, $discounts) {
        $article_discounts = ArticleDiscount::where('article_id', $article->id)
                                            ->pluck('id');
        ArticleDiscount::destroy($article_discounts);                                   
        if ($discounts) {
            foreach ($discounts as $discount) {
                $discount = (object) $discount;
                if ($discount->percentage != '') {
                    ArticleDiscount::create([
                        'percentage' => $discount->percentage,
                        'article_id' => $article->id,
                    ]);
                }
            }
        }
    }

    static function setDescriptions($article, $descriptions) {
        $article_descriptions = Description::where('article_id', $article->id)
                                            ->get();
        foreach ($article_descriptions as $article_description) {
            $article_description->delete();
        }
        if ($descriptions) {
            foreach ($descriptions as $description) {
                // $description = (array) $description;
                if (isset($description['content']) && !is_null($description['content'])) {
                    Description::create([
                        'title'      => isset($description['title']) ? StringHelper::onlyFirstWordUpperCase($description['title']) : null,
                        'content'    => $description['content'],
                        'article_id' => $article->id,
                    ]);
                }
            }
        }
    }

    static function setSizes($article, $sizes_id) {
        $article->sizes()->sync([]);
        if ($sizes_id) {
            foreach ($sizes_id as $size_id) {
                $article->sizes()->attach($size_id);
            }
        }
    }

    static function setColors($article, $colors) {
        $article->colors()->sync([]);
        if ($colors) {
            foreach ($colors as $color) {
                $article->colors()->attach($color['id']);
            }
        }
    }

    static function setCondition($article, $condition_id) {
        if ($condition_id) {
            $article->condition_id = $condition_id;
            $article->save();
        }
    }

    static function deleteVariants($article) {
        foreach ($article->variants as $variant) {
            $variant->delete();
        }
    }

    static function getStockVariantToAdd($variant) {
        if (isset($variant['stock_to_add']) && $variant['stock_to_add'] != '') {
            return $variant['stock'] + $variant['stock_to_add'];
        }
        return $variant['stock'];
    }

    static function slug($name, $ignore_id = null) {
        $index = 1;
        $slug = Str::slug($name);
        $repeated_article = Article::where('user_id', UserHelper::userId())
                                    ->where('slug', $slug);
        if (!is_null($ignore_id)) {
            $repeated_article = $repeated_article->where('id', '!=', $ignore_id);
        }
        $repeated_article = $repeated_article->first();
        
        while (!is_null($repeated_article)) {
            $slug = substr($slug, 0, strlen($name));
            $slug .= '-'.$index;
            $repeated_article = Article::where('user_id', UserHelper::userId())
                                        ->where('slug', $slug)
                                        ->first();
            $index++;
        }
        return $slug;
    }

    static function setArticlesKey($articles) {
        foreach ($articles as $article) {
            if ($article->pivot->variant_id) {
                $article->key = $article->id . '-' . $article->pivot->variant_id;
            } else {
                $article->key = $article->id;
            }
        }
        return $articles;
    }

    static function setArticlesKeyAndVariant($articles) {
        foreach ($articles as $article) {
            if (isset($article->pivot) && $article->pivot->variant_id) {
                foreach ($article->variants as $variant) {
                    if ($variant->id == $article->pivot->variant_id) {
                        $article->variant = $variant;
                    }
                }
                $article->key = $article->id . '-' . $article->pivot->variant_id;
            } else {
                $article->key = $article->id;
            }
        }
        return $articles;
    }

    static function getFullArticle($article_id) {
        $article = Article::where('id', $article_id)
                            ->withAll()
                            ->first();
        $article = Self::setPrices([$article])[0];
        return $article;
    }

    static function price($price) {
        $pos = strpos($price, '.');
        if ($pos != false) {
            $centavos = explode('.', $price)[1];
            $new_price = explode('.', $price)[0];
            if ($centavos != '00') {
                $new_price += ".$centavos";
                return '$'.number_format($new_price, 2, ',', '.');
            } else {
                return '$'.number_format($new_price, 0, '', '.');           
            }
        } else {
            return '$'.number_format($price, 0, '', '.');
        }
    }

    static function getFirstImage($article) {
        if (count($article->images) >= 1) {
            $first_image = $article->images[0]->hosting_url;
            foreach ($article->images as $image) {
                if ($image->first != 0) {
                    $first_image = $image->hosting_url;
                }
            }
            if (env('APP_ENV') == 'production') {
                $position = strpos($first_image, 'storage');
                $first = substr($first_image, 0, $position);
                $end = substr($first_image, $position);
                return $first.'public/'.$end;
            }
            return $first_image;
        }
        return null;
    }
}