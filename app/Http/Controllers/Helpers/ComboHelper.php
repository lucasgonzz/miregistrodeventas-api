<?php

namespace App\Http\Controllers\Helpers;

use App\Combo;

class ComboHelper {

	static function attachArticles($combo, $articles) {
		$combo->articles()->sync([]);
		foreach ($articles as $article) {
			$combo->articles()->attach($article['id'], [
														'amount' => $article['pivot']['amount']
													]);
		}
	}

	static function setArticles($combos) {
		foreach ($combos as $combo) {
			foreach ($combo->articles as $article) {
				$article->amount = $article->pivot->amount;
			}
		}
		return $combos;
	}

	static function getFullModel($id) {
		$combo = Combo::where('id', $id)
						->with('articles')
						->first();
		$combo = Self::setArticles([$combo])[0];
		return $combo;
	}

    static function hasArticle($combo, $_article) {
        $has_article = false;
        foreach ($combo->articles as $article) {
            if ($article->id == $_article['id']) {
                $has_article = true;
                break;
            }
        }
        return $has_article;
    }

}