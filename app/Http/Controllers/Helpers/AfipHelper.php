<?php

namespace App\Http\Controllers\Helpers;

class AfipHelper {

	static function getImporteIva($article) {
		return ($article->pivot->price * $article->pivot->amount) * 0.21;
		if ($this->user()->iva == 'Responsable inscripto') {
			return ($article->pivot->price * $article->pivot->amount) * 0.21;
		}
	}

	static function getImporteItem($article) {
		return ($article->pivot->price * $article->pivot->amount) + Self::getImporteIva($article);
		if ($this->user()->iva == 'Responsable inscripto') {
			return ($article->pivot->price * $article->pivot->amount) + Self::getImporteIva($article);
		}
	}

	static function getImporteGravado($article) {
		return $article->pivot->price * $article->pivot->amount;
		if ($this->user()->iva == 'Responsable inscripto' && $sale->client->iva == 'Responsable inscripto') {
			return $article->pivot->price * $article->pivot->amount;
		}
	}

	static function getTipoComprobante($sale) {
		return 1;
		if ($this->user()->iva == 'Responsable inscripto') {
			if ($sale->client->iva == 'Responsable inscripto') {
				return 1;
			} else if ($sale->client->iva == 'Monotributo') {
				return 6;
			}
		}
	}

}