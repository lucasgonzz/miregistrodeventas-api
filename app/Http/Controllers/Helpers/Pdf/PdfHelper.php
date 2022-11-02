<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Article;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Carbon\Carbon;

class PdfHelper {

	static function logo($instance) {
        // Logo
        $user = UserHelper::getFullModel();
        if (!is_null($user->image_url)) {
        	$instance->Image(ImageHelper::image($user), 5, 5, 0, 27);
        }
		
		$instance->SetFont('Arial', '', 10);

		// Company name
		$instance->x = 5;
		$instance->y = 30;
		$instance->Cell(60, 5, $user->company_name, $instance->b, 0, 'L');

		if (!is_null($user->afip_information) && !is_null($user->afip_information->iva_condition)) {
			$instance->x = 65;
			$instance->Cell(40, 5, $user->afip_information->iva_condition->name, $instance->b, 1, 'L');
		}
		if (count($user->addresses) >= 1) {
			$instance->x = 5;
			$address = $user->addresses[0];
			$address = $address->street.' '.$address->street_number;
			$instance->Cell(100, 5, $address, $instance->b, 1, 'L');
		}
		if (!is_null($user->online)) {
			$instance->x = 5;
			$instance->Cell(100, 5, $user->online, $instance->b, 1, 'L');
		}
	}

	static function comerciocityInfo($instance, $y = 290) {
	    $instance->y = $y;
	    $instance->x = 5;
	    $instance->SetFont('Arial', '', 8);
		$instance->Cell(200, 5, 'Comprobante creado con el sistema de control de stock ComercioCity - comerciocity.com', $instance->b, 0, 'C');
	}

	static function tableHeader($instance, $fields) {
		$instance->SetFont('Arial', 'B', 12);
		$instance->x = 5;
		$instance->y += 5;
		$instance->SetLineWidth(.4);
		foreach ($fields as $field) {
			$instance->Cell($field['width'], 10, $field['text'], 'BT', 0, 'C');
		}
		$instance->y += 10;
	}

	static function numeroFecha($instance, $num, $date) {
		$instance->SetFont('Arial', 'B', 14);
		$instance->x = 105;
		$instance->y = 5;

		// Numero
		$instance->Cell(50, 10, 'N° '.$num, $instance->b, 0, 'L');
		// $instance->x = 105;
		$instance->Cell(50, 10, date_format($date, 'd/m/Y'), $instance->b, 0, 'R');
	}

	static function title($instance, $title) {
		$instance->SetFont('Arial', 'B', 14);
		$instance->x = 105;
		$instance->y = 15;
		$instance->Cell(50, 10, $title, $instance->b, 0, 'L');
	}

	static function commerceInfo($instance) {
		$user = UserHelper::getFullModel();
		$instance->SetFont('Arial', '', 10);
		$instance->x = 105;
		$instance->y = 30;
		$instance->Cell(100, 5, 'Cuit: '.$user->afip_information->cuit, $instance->b, 0, 'L');
		$instance->x = 105;
		$instance->y += 5;
		$instance->Cell(100, 5, 'Ingresos Brutos: '.$user->afip_information->ingresos_brutos, $instance->b, 0, 'L');
		$instance->x = 105;
		$instance->y += 5;
		$instance->Cell(100, 5, 'Inicio Actividades: '.date_format($user->afip_information->inicio_actividades, 'd/m/Y'), $instance->b, 0, 'L');
		// $instance->lineCommerce();
	}

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