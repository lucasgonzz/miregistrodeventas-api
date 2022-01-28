<?php

namespace App\Http\Controllers\Helpers;

class AfipHelper {

	static function getNumeroComprobante($wsfe, $punto_venta, $cbte_tipo) {
		$pto_vta = [
            'PtoVta'    => $punto_venta,
            'CbteTipo'  => $cbte_tipo
        ];
        $result = $wsfe->FECompUltimoAutorizado($pto_vta);
        return $result->FECompUltimoAutorizadoResult->CbteNro + 1;
	}

	static function getImportes($sale) {
        $items = [];
        $importe_gravado  = 0;
        $importe_iva      = 0;
        $importe_subtotal = 0;
        $importe_total    = 0;
        foreach ($sale->articles as $article) {
            $items['item'] = [
                'unidadesMtx'           => 1,
                'codigoMtx'             => '7790001001054',
                'codigo' => !is_null($article->bar_code) ? $article->bar_code : substr($article->name, 0,3),
                'descripcion'           => $article->name,
                'cantidad'              => $article->pivot->amount,
                'codigoUnidadMedida'    => 7,
                'precioUnitario'        => $article->pivot->price,
                'importeBonificacion'   => 0,
                'codigoCondicionIVA'    => 5,
                'importeItem'           => Self::getImporteItem($article),
                'importeIVA'            => Self::getImporteIva($article),
            ];
            // Checke que sea factura A para discriminar el IVA
            // if (AfipHelper::getTipoComprobante($sale) == 1) {
            //     $item['importeIVA'] = AfipHelper::getImporteIva($article);
            // }
            $importe_gravado    += Self::getImporteGravado($article);
            $importe_iva        += Self::getImporteIva($article);
            $importe_subtotal   += Self::getImporteGravado($article);
            $importe_total      += Self::getImporteItem($article);
        }
        return [
        	'importe_gravado' 	=> $importe_gravado,
        	'importe_iva' 		=> $importe_iva,
        	'importe_subtotal' 	=> $importe_subtotal,
        	'importe_total' 	=> $importe_total,
        ];
	}

	static function getDocType($slug) {
		$doc_type = [
            'Cuit' => 80,
            'Cuil' => 86,
            'CDI' => 87,
            'LE' => 89,
            'LC' => 90,
            'CI Extranjera' => 91,
            'en trámite' => 92,
            'Acta Nacimiento' => 93,
            'CI Bs. As. RNP' => 95,
            'DNI' => 96,
        ];
        return $doc_type[$slug];
	}

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