<?php

namespace App\Http\Controllers\Helpers;
use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageHelper {

	static function image($model = null, $from_model = false, $cropped = true) {
		$image_url = ''; 
		if (is_null($model)) {
			$model = UserHelper::getFullModel();
		}
		if (!$from_model) {
			$image_url = $model->hosting_image_url;
		} else {
			$image_url = $model->{$from_model}->hosting_image_url;
		}
		if ($image_url) {
			return $image_url;
		}
	}

	static function saveHostingImage($cloudinary_url) {
        $array = explode('/', $cloudinary_url);
        $img_prefix = $array[0].'/'.$array[1];
        $name = $array[2];
        $format = explode('.', $name);
        $name = $format[0].'.jpeg';
        $url_cloudinary = 'https://res.cloudinary.com/lucas-cn/image/upload/c_crop,g_custom,q_auto,f_auto/'.$img_prefix.'/'.$name; 
        $file_headers = get_headers($url_cloudinary);
        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
        	return null;
        }
        Storage::disk('public')->put($name, file_get_contents($url_cloudinary));
        return env('APP_URL').'/storage/'.$name;
	}
}