<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Article;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Carbon\Carbon;

class SalePdfHelper {

	static function clientInfo($instance, $client) {
		if ($client) {
			$instance->SetFont('Arial', '', 10);
			$instance->x = $instance->start_x;
			$instance->y = 35;
			$instance->SetFont('Arial', 'B', 10);
			$instance->Cell(20, 5, 'Cliente:', $instance->b, 0, 'L');
			$instance->SetFont('Arial', '', 10);
			$instance->Cell(85, 5, $client->name, $instance->b, 0, 'L');

			if ($client->address != '') {
				$instance->y += 5;
				$instance->x = $instance->start_x;
				$instance->SetFont('Arial', 'B', 10);
				$instance->Cell(20, 5, 'Direccion:', $instance->b, 0, 'L');
				$instance->SetFont('Arial', '', 10);
				$instance->Cell(80, 5, $client->address, $instance->b, 0, 'L');
			} 

			if ($client->phone != '') {
				$instance->y += 5;
				$instance->x = $instance->start_x;
				$instance->SetFont('Arial', 'B', 10);
				$instance->Cell(20, 5, 'Telefono:', $instance->b, 0, 'L');
				$instance->SetFont('Arial', '', 10);
				$instance->Cell(80, 5, $client->phone, $instance->b, 0, 'L');
			} 
			
			// if (!is_null($client->iva_condition)) {
			// 	$instance->y += 5;
			// 	$instance->x = $instance->start_x;
			// 	$instance->SetFont('Arial', 'B', 10);
			// 	$instance->Cell(23, 5, 'Con. de IVA:', $instance->b, 0, 'L');
			// 	$instance->SetFont('Arial', '', 10);
			// 	$instance->Cell(77, 5, $client->iva_condition->name, $instance->b, 0, 'L');
			// }

			if (!is_null($client->location)) {
				$instance->y += 5;
				$instance->x = $instance->start_x;
				$instance->SetFont('Arial', 'B', 10);
				$instance->Cell(20, 5, 'Localidad:', $instance->b, 0, 'L');
				$instance->SetFont('Arial', '', 10);
				$instance->Cell(88, 5, $client->location->name, $instance->b, 0, 'L');
			}
			// if ($client->cuit != '') {
			// 	$instance->y += 5;
			// 	$instance->x = $instance->start_x;
			// 	$instance->SetFont('Arial', 'B', 10);
			// 	$instance->Cell(12, 5, 'CUIT:', $instance->b, 0, 'L');
			// 	$instance->SetFont('Arial', '', 10);
			// 	$instance->Cell(88, 5, $client->cuit, $instance->b, 0, 'L');
			// }
			// $instance->y += 5;
		}
	}

	static function currentAcountInfo($instance, $current_acount, $client_id, $compra_actual){
		$saldo_anterior = CurrentAcountHelper::getSaldo('client', $client_id, $current_acount);
		$instance->y = 35;
		$instance->x = 105;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Saldo anterior:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, '$'.Numbers::price($saldo_anterior), 0, 'L');

		$instance->x = 105;
		$instance->y += 5;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Compra actual:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, '$'.Numbers::price($compra_actual), 0, 'L');

		$instance->x = 105;
		$instance->y += 5;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Saldo:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, '$'.Numbers::price($saldo_anterior + $compra_actual), 0, 'L');

		if (!is_null($instance->sale->employee)) {
			$vendedor = $instance->sale->employee->name;
		} else {
			$vendedor = UserHelper::getFullModel()->name;
		}
		$instance->x = 105;
		$instance->y += 5;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Vendedor:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, $vendedor, 0, 'L');
	}
	
}