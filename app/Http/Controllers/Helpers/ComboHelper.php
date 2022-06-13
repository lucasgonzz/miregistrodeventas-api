<?php

namespace App\Http\Controllers\Helpers;

use App\Combo;

class ComboHelper {

	static function attachArticles($combo, $articles) {
		foreach ($articles as $article) {
			$article = (object)$article;
			$combo->articles()->attach($article->id);
		}
	}

	static function getFullModel($id) {
		$combo = Combo::where('id', $id)
						->with('articles')
						->first();
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