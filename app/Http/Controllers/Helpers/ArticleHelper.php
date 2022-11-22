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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ArticleHelper {

    static function setPrices($articles) {
        $user = UserHelper::user();
        foreach ($articles as $article) {
            $cost = $article->cost;
            if ($article->cost_in_dollars) {
                $cost = $cost * $user->dollar;
            }
            $last_provider_percentage_gain = Self::lastProviderPercentageGain($article);
            if ((!is_null($last_provider_percentage_gain) && $article->apply_provider_percentage_gain) || $article->percentage_gain) {
                $article->price = null;
                $article->save();
            }
            $price = 0;
            if (is_null($article->price) || $article->price == '') {

                if ($article->apply_provider_percentage_gain) {
                    if (!is_null($article->provider_price_list_id)) {
                        $price = Numbers::redondear($cost + ($cost * Numbers::percentage($article->provider_price_list->percentage)));
                    } else if (!is_null($last_provider_percentage_gain)) {
                        $price = Numbers::redondear($cost + ($cost * Numbers::percentage($last_provider_percentage_gain)));
                    }
                }

                if (!is_null($article->percentage_gain)) {
                    if ($price == 0) {
                        $price = Numbers::redondear($cost + ($cost * Numbers::percentage($article->percentage_gain)));
                    } else {
                        $price = Numbers::redondear($price + ($price * Numbers::percentage($article->percentage_gain)));
                    }
                }
            } 
            if (!$user->configuration->iva_included && Self::hasIva($article)) {
                if ($price == 0) {
                    $price = Numbers::redondear($article->price + ($article->price * Numbers::percentage($article->iva->percentage)));
                } else {
                    $price = Numbers::redondear($price + ($price * Numbers::percentage($article->iva->percentage)));
                }
            }
            if ($price > 0) {
                $article->price = $price;
            }
            if (count($article->discounts) >= 1) {
                foreach ($article->discounts as $discount) {
                    $article->price = Numbers::redondear($article->price - ($article->price * Numbers::percentage($discount->percentage)));
                }
            }
        }
        return $articles;
    }

    static function getById($articles_ids) {
        $models = [];
        foreach ($articles_ids as $id) {
            $models[] = ArticleHelper::getFullArticle($id);
        }
        return $models;
    }

    static function lastProviderPercentageGain($article) {
        $last_provider = Self::lastProvider($article);
        if (!is_null($last_provider) && !is_null($last_provider->percentage_gain)) {
            return $last_provider->percentage_gain;
        }
        return null;
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
        Log::info('tiene stock de '.$article->stock);
        Log::info('llego stock de '.$request->stock);
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
            $first_image = $article->images[0]->url;
            foreach ($article->images as $image) {
                if ($image->first != 0) {
                    $first_image = $image->url;
                }
            }
            return 'https://res.cloudinary.com/lucas-cn/image/upload/c_crop,g_custom/r_20/co_rgb:6F6F6F,e_shadow:50,x_-20,y_20/'.$first_image;
        }
        return null;
    }
}