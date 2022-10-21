<?php

namespace App\Http\Controllers\Helpers;
use App\Category;
use App\Http\Controllers\Helpers\UserHelper;
use App\Iva;
use App\SubCategory;
use Illuminate\Support\Facades\Log;

class ImportHelper {

	static function getSubcategoryId($row) {
		if ($row['categoria'] != '' && $row['sub_categoria'] != '') {
			$category = Category::where('user_id', UserHelper::userId())
								->where('name', $row['categoria'])
								->first();
			if (is_null($category)) {
				$category = Category::create([
					'name' 		=> $row['categoria'],
					'user_id' 	=> UserHelper::userId(),
				]);
			}
			$sub_category = SubCategory::where('user_id', UserHelper::userId())
										->where('name', $row['sub_categoria'])
										->where('category_id', $category->id)
										->first();
			if (is_null($sub_category)) {
				$sub_category = SubCategory::create([
					'name' 			=> $row['sub_categoria'],
					'category_id' 	=> $category->id,
					'user_id'		=> UserHelper::userId(),
				]);
			}
			Log::info('Retornando '.$sub_category->name.' con id: '.$sub_category->id.' para '.$row['nombre']);
			return $sub_category->id;
		}
		return null;
	}

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
		if ($row['iva'] != '' || $row['iva'] == '0' || $row['iva'] == 0) {
			$iva = Iva::where('percentage', $row['iva'])
						->first();
			if (is_null($iva)) {
				$iva = Iva::create([
					'percentage' => $row['iva'],
				]);
			}
			return $iva->id;
		}
		return 2;
	}
}