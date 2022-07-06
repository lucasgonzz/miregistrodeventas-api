<?php

namespace App\Http\Controllers\Helpers;
use Illuminate\Support\Facades\Log;

class ImportHelper {

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

	static function getIva($row) {
		Log::info('buscando iva '.$row['iva']);
		$ivas = [
			'0.27' 	=> 1,
			'0.21' 	=> 2,
			'0.105' => 3,
			'0.05' 	=> 4,
			'0.025' => 5,
			'0' 	=> 6,
		];
		Log::info('Retornando iva '.$ivas[''.$row['iva']]);

		return $ivas[''.$row['iva']];
	}
}