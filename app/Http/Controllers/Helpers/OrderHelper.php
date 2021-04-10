<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Helpers\ArticleHelper;


class OrderHelper {
	static function setArticlesKey($orders) {
		foreach ($orders as $order) {
			$order->articles = ArticleHelper::setArticlesKey($order->articles);
		}
		return $orders;
	}
}