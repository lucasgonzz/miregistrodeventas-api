<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Sale;
use App\Client;

class PdfArticleHelper {

	static function stock($article) {
		if (is_null($article->stock)) {
			return '-';
		} else {
			if ($article->uncontable == 0) {
				return substr($article->stock, 0, -3);
			} else {
				if ($article->measurement == 'gramo') {
					$measurement= 'gr';
				} else {
					$measurement= 'kg';
				}
				if (strripos($article->stock, '.00') != false) {
					return substr($article->stock, 0, -3)." $measurement";
				} else {
					return "$article->stock $measurement";
				}
			}
		}
	}

	static function amount($article) {
		return $article->pivot->amount;
	}

	static function getSubTotalCost($article) {
		return $article->pivot->cost * $article->pivot->amount;
	}

	static function getSubTotalPrice($article) {
		return $article->pivot->price * $article->pivot->amount;
	}

}

