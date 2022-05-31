<?php

namespace App\Http\Controllers\Helpers;

class ImageHelper {

	static function image($model, $from_model = false, $cropped = true) {
			$url = '';
			$image_url = ''; 
			if (!$from_model) {
				$image_url = $model->image_url;
			} else {
				$image_url = $model->{$from_model}->image_url;
			}
			if ($image_url) {
				if ($cropped) {
					$url = "https://res.cloudinary.com/lucas-cn/image/upload/c_crop,g_custom/${image_url}";
				} else {
					$url = "https://res.cloudinary.com/lucas-cn/image/upload/${image_url}";
				}
			}
			return $url;
		}
}