<?php

namespace App\Http\Controllers\Helpers;

use App\Iva;

class IvaHelper {

	static function getModelBy($prop_name, $prop_value) {
		$iva = Iva::where('percentage', $percentage)
						->first();
		return $iva;
	}

}