<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Support\Str;

class ArticleHelper {

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

    static function slug($name) {
        $slug = Str::slug($name);
        $repeated_article = Article::where('user_id', UserHelper::userId())
                                    ->where('slug', $slug)
                                    ->first();
        if (!is_null($repeated_article)) {
            $slug .= '_';
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

	static function getFullArticle($article_id) {
		$article = Article::where('id', $article_id)
                            ->with('images')
                            ->with('sub_category')
                            ->with('variants')
                            ->with('specialPrices')
                            ->with(['providers' => function($q) {
                                $q->orderBy('cost', 'asc');
                            }])
                            ->first();
        return $article;
    }
}