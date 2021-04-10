<?php

namespace App\Http\Controllers\Helpers;

class StringHelper {
	static function modelName($name) {
		return ucfirst(strtolower($name));
	}
}