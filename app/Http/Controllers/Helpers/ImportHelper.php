<?php

namespace App\Http\Controllers\Helpers;
use App\Category;
use App\Http\Controllers\Helpers\UserHelper;
use App\Iva;
use App\SubCategory;
use Illuminate\Support\Facades\Log;

class ImportHelper {

	static function getSubcategoryId($categoria, $sub_categoria) {
		if ($categoria != '' && $sub_categoria != '') {
			$category = Category::where('user_id', UserHelper::userId())
								->where('name', $categoria)
								->first();
			if (is_null($category)) {
				$category = Category::create([
					'name' 		=> $categoria,
					'user_id' 	=> UserHelper::userId(),
				]);
			}
			$sub_category = SubCategory::where('user_id', UserHelper::userId())
										->where('name', $sub_categoria)
										->where('category_id', $category->id)
										->first();
			if (is_null($sub_category)) {
				$sub_category = SubCategory::create([
					'name' 			=> $sub_categoria,
					'category_id' 	=> $category->id,
					'user_id'		=> UserHelper::userId(),
				]);
			}
			return $sub_category->id;
		}
		return null;
	}

	static function getColumnValue($row, $key, $columns) {
		if (isset($columns[$key])) {
			return $row[$columns[$key]];
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

	static function saveProvider($proveedor, $ct) {
		if ($proveedor != 'Sin especificar' && $proveedor != '') {
	        $data = [
                'name'      => $proveedor,
                'user_id'   => $ct->userId(),
            ];
	        $ct->createIfNotExist('providers', 'name', $proveedor, $data);
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

	static function getIvaId($iva, $article = null) {
		if (!is_null($iva)) {
			if ($iva != '' || $iva == '0' || $iva == 0) {
				$_iva = Iva::where('percentage', $iva)
							->first();
				if (is_null($_iva)) {
					$_iva = Iva::create([
						'percentage' => $iva,
					]);
				}
				return $_iva->id;
			}
		} else if (!is_null($article)) {
			if (!is_null($article->iva_id)) {
				return $article->iva_id;
			}
		}
		return 2;
	}
}