<?php

namespace App\Http\Controllers\Helpers;

class StringHelper {
	static function modelName($name, $ucwords = false) {
		if ($ucwords) {
			return ucwords(strtolower($name));
		}
		return ucfirst(strtolower($name));
	}
}