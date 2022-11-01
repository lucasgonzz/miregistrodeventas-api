<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Http\Controllers\Helpers\UserHelper;
use Carbon\Carbon;

class ArticleFilterHelper {

	static function filter($filter) {
		$models = Article::where('user_id', UserHelper::userId());
		
		if ($filter['sub_category_id'] != 0) {
			$models = $models->where('sub_category_id', $filter['sub_category_id']);
		}
		
		if ($filter['with_images']) {
			$models = $models->whereHas('images');
		}
		
		if ($filter['featured']) {
			$models = $models->whereNotNull('featured');
		}

		$models = $models->get();

		if ($filter['provider_id'] != 0) {
			$result = [];
			foreach ($models as $model) {
				if (count($model->providers) >= 1) {
					if ($model->providers[count($model->providers)-1]->id == $filter['provider_id']) {
						$result[] = $model;
					}
				}
			}
			$models = $result;
		}

		return $models;
	}
	
}