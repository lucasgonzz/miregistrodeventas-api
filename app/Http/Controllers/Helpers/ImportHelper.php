<?php

namespace App\Http\Controllers\Helpers;
use App\Iva;
use Illuminate\Support\Facades\Log;

class ImportHelper {

	// static function saveCategory($row, $ct) {
	// 	if ($row['localidad'] != 'Sin especificar' && $row['localidad'] != '') {
			
	// 	}
	// }

	static function saveLocation($row, $ct) {
		if ($row['localidad'] != 'Sin especificar' && $row['localidad'] != '') {
	        $data = [
                'name'      => $row['localidad'],
                'user_id'   => $ct->userId(),
            ];
	        $ct->createIfNotExist('locations', 'name', $row['localidad'], $data);
	    }
	}

	static function saveProvider($row, $ct) {
		if ($row['proveedor'] != 'Sin especificar' && $row['proveedor'] != '') {
	        $data = [
                'name'      => $row['proveedor'],
                'user_id'   => $ct->userId(),
            ];
	        $ct->createIfNotExist('providers', 'name', $row['proveedor'], $data);
	    }
	}

	static function savePriceType($row, $ct) {
		if ($row['tipo_de_precio'] != 'Sin especificar' && $row['tipo_de_precio'] != '') {
	        $data = [
                'name'      => $row['tipo_de_precio'],
                'user_id'   => $ct->userId(),
            ];
	        $ct->createIfNotExist('price_types', 'name', $row['tipo_de_precio'], $data);
	    }
	}

	static function getIvaId($row) {
		if ($row['iva'] == '0' || $row['iva'] == 0 || $row['iva'] != '') {
			$iva = Iva::where('percentage', $row['iva'])
						->first();
			if (is_null($iva)) {
				$iva = Iva::create([
					'percentage' => $row['iva'],
				]);
			}
		}
		return 2;
	}
}