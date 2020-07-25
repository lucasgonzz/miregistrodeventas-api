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
		if ($article->uncontable == 0) {
			return $article->pivot->amount;
		} else {
			if ($article->pivot->measurement == 'gramo') {
				$measurement= 'gr';
			} else {
				$measurement= 'kg';
			}
			return $article->pivot->amount . ' ' . $measurement;
		}
	}

	static function getSubTotalCost($article) {
		if ($article->uncontable == 0) {
			return $article->pivot->cost * $article->pivot->amount;
		} else {
			if ($article->pivot->measurement == $article->measurement) {
				return $article->pivot->cost * $article->pivot->amount;
			} else {
				return $article->pivot->cost * $article->pivot->amount / 1000;
			}
		}
	}

	static function getSubTotalPrice($article) {
		if ($article->uncontable == 0) {
			return $article->pivot->price * $article->pivot->amount;
		} else {
			if ($article->pivot->measurement == $article->measurement) {
				return $article->pivot->price * $article->pivot->amount;
			} else {
				return $article->pivot->price * $article->pivot->amount / 1000;
			}
		}
	}

}

