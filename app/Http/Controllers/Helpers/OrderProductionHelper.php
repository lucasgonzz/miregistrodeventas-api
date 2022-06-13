<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Notifications\OrderProductionNotification;
use App\OrderProductionStatus;
use Carbon\Carbon;

class OrderProductionHelper {

	static function getStatusId($name) {
		$status = OrderProductionStatus::where('name', $name)->first();
		return $status->id;
	}

	static function sendCreatedMail($order_production, $send_mail) {
		if ($send_mail && $order_production->budget->client->email != '') {
			$subject = 'ORDEN DE PRODUCCION CREADA';
			$line = 'Empezamos a trabajar en tu pedido, actualmente se encuentra en la primer fase, nos comunicaremos por este medio para informarte sobre cualquier actualización en el estado de producción.';
			$order_production->budget->client->notify(new OrderProductionNotification($order_production, $subject, $line));
		}
	}

	static function sendUpdatedMail($order_production) {
		if ($order_production->budget->client->email != '') {
			$subject = 'ORDEN DE PRODUCCION ACTUALIZADA';
			$line = 'Nos alegra informarte que tu pedido avanzo a la siguiente fase, nos comunicaremos por este medio para informarte sobre cualquier actualización en el estado de producción.';
			$order_production->budget->client->notify(new OrderProductionNotification($order_production, $subject, $line));
		}
	}

}