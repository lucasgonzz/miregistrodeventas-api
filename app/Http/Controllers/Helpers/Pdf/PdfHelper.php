<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Article;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PdfHelper {

	static function header($instance, $data) {
		Self::logo($instance);
		Self::title($instance, $data['title']);
		Self::numeroFecha($instance, $data['num'], $data['date']);
		Self::commerceInfo($instance);
		
		Self::commerceInfoLine($instance);

		Self::modelInfo($instance, $data['model_info'], $data['model_props']);
		if (isset($data['current_acount'])) {
			Self::currentAcountInfo($instance, $data['current_acount'], $data['client_id'], $data['compra_actual']);
		}
		if (isset($data['fields'])) {
			Self::tableHeader($instance, $data['fields']);
		}
	}

	static function logo($instance) {
        // Logo
        $user = UserHelper::getFullModel();
        if (!is_null($user->hosting_image_url)) {
        	$image = $user->image_url;
        	if (!$user->from_cloudinary) {
        		$image = $user->hosting_image_url;
        	}
        	$instance->Image($image, 5, 5, 40, 25);
        	// $instance->Image('https://images.hola.com/imagenes/bloques/videoloop-portada-tematica/egenia-silva-oscura-2t.jpg', 5, 5, 40, 25);
        }
		
		$instance->SetFont('Arial', 'B', 9);

		// Razon social
		$instance->y = 5;
		if (!is_null($user->afip_information)) {
			if (!is_null($user->afip_information->razon_social)) {
				$instance->x = 45;
				$instance->Cell(40, 5, $user->afip_information->razon_social, $instance->b, 1, 'L');
			}
		}

		// Condicion IVA
		if (!is_null($user->afip_information) && !is_null($user->afip_information->iva_condition)) {
			$instance->x = 45;
			$instance->Cell(40, 5, $user->afip_information->iva_condition->name, $instance->b, 1, 'L');
		}

		// Telefono
		$instance->y += 5;
		if (!is_null($user->phone)) {
			$instance->x = 45;
			$instance->SetFont('Arial', 'B', 10);
			$instance->Cell(10, 5, 'Tel: ', $instance->b, 0, 'L');

			$instance->SetFont('Arial', '', 10);
			$instance->Cell(50, 5, $user->phone, $instance->b, 1, 'L');
		}

		// Sitio Web
		if (!is_null($user->online)) {
			$instance->x = 45;
			$instance->SetFont('Arial', 'B', 10);
			$instance->Cell(10, 5, 'Web: ', $instance->b, 0, 'L');

			$instance->SetFont('Arial', '', 10);
			$instance->Cell(50, 5, Self::getWebUrl($user->online), $instance->b, 1, 'L');
		}
		// $instance->Line(5, 5, 101, 5);
		// $instance->Line(101, 5, 101, 45);
		// $instance->Line(101, 45, 5, 45);
		// $instance->Line(5, 45, 5, 5);
	}

	static function firma($instance) {
		$instance->x = 75;
		$instance->y += 10;
		$instance->SetFont('Arial', '', 11);
		$instance->Cell(50, 7, 'Firma', 'T', 1, 'C');
	}

	static function numeroFecha($instance, $num, $date) {
		$instance->SetFont('Arial', 'B', 14);
		$start_y = 5;
		$instance->y = 5;
		$instance->x = 120;
		// Numero
		$instance->Cell(40, 10, 'N° '.$num, $instance->b, 0, 'L');
		$instance->Cell(40, 10, date_format($date, 'd/m/Y'), $instance->b, 1, 'R');
	}

	static function title($instance, $title) {
		$instance->SetFont('Arial', 'B', 12);
		$start_y = 5;
		$start_x = 90;
		$width = 30;
		$instance->y = $start_y;
		$instance->x = $start_x;
	    $instance->MultiCell(
			$width, 
			5, 
			$title, 
	    	$instance->b, 
	    	'C', 
	    	false
	    );
	    $finish_x = $start_x + $width;
	    $finish_y = $start_y + 15;
		$instance->Line($start_x, $start_y, $finish_x, $start_y);
		$instance->Line($finish_x, $start_y, $finish_x, $finish_y);
		$instance->Line($finish_x, $finish_y, $start_x, $finish_y);
		$instance->Line($start_x, $finish_y, $start_x, $start_y);
	}

	static function commerceInfo($instance) {
		$user = UserHelper::getFullModel();
		$instance->y += 5;
		$start_y = $instance->y;
		// Direccion
		if (count($user->addresses) >= 1) {
			$address = $user->addresses[0];
			$instance->x = 105;
			$instance->SetFont('Arial', 'B', 10);
			$instance->Cell(12, 5, 'Direc: ', $instance->b, 0, 'L');

			$address_text = "{$address->street} {$address->street_number}, {$address->city}, {$address->province}";
			$instance->SetFont('Arial', '', 10);
			$instance->Cell(88, 5, $address_text, $instance->b, 0, 'L');
		}

		// Correo
		$instance->x = 105;
		$instance->y += 5;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(12, 5, 'Email:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(88, 5, $user->email, $instance->b, 1, 'L');

		$instance->y += 2;
	}

	static function commerceInfoLine($instance) {
		$instance->Line(5, 5, 205, 5);
		$instance->Line(205, 5, 205, 30);
		$instance->Line(205, 30, 5, 30);
		$instance->Line(5, 30, 5, 5);
		$instance->Line(105, 20, 105, 30);
	}

	static function modelInfo($instance, $model, $props) {
	    $instance->x = 5;
	    $start_y = $instance->y;
	    $instance->SetFont('Arial', '', 10);
	    foreach ($props as $prop) {
	    	$instance->x = 5;
			$instance->Cell(100, 5, $prop['text'].': '.Self::getPropValue($model, $prop), $instance->b, 1, 'L');
	    }
	    $instance->Line(5, $start_y, 105, $start_y);
	    $instance->Line(105, $start_y, 105, $instance->y);
	    $instance->Line(105, $instance->y, 5, $instance->y);
	    $instance->Line(5, $instance->y, 5, $start_y);
	}

	static function getPropValue($model, $prop) {
		if (str_contains($prop['key'], '.')) {
			$relation = substr($prop['key'], 0, strpos($prop['key'], '.'));
			$key = substr($prop['key'], strpos($prop['key'], '.')+1, strlen($prop['key']));
			if (!is_null($model->{$relation})) {
				return $model->{$relation}->{$key};
			} 
			return '';
		}
		return $model->{$prop['key']};
	}

	static function comerciocityInfo($instance, $y = 290) {
	    $instance->y = $y;
	    $instance->x = 5;
	    $instance->SetFont('Arial', '', 8);
		$instance->Cell(200, 5, 'Comprobante creado con el sistema de control de stock ComercioCity - comerciocity.com', $instance->b, 0, 'C');
	}

	static function total($instance, $total) {
	    $instance->x = 155;
	    $instance->SetFont('Arial', 'B', 12);
		$instance->Cell(50, 10, 'Total: $'.Numbers::price($total), $instance->b, 1, 'R');
	}

	static function tableHeader($instance, $fields) {
		$instance->SetFont('Arial', 'B', 12);
		$instance->x = 5;
		$instance->y += 5;
		$instance->SetLineWidth(.4);
		foreach ($fields as $text => $width) {
			$instance->Cell($width, 10, $text, 'BT', 0, 'C');
		}
		$instance->y += 10;
	}

	static function clientInfo($instance, $client) {
		if ($client) {
			$instance->SetFont('Arial', '', 10);
			$instance->x = 5;
			$instance->SetFont('Arial', 'B', 10);
			$instance->Cell(20, 5, 'Cliente:', $instance->b, 0, 'L');
			$instance->SetFont('Arial', '', 10);
			$instance->Cell(85, 5, $client->name, $instance->b, 0, 'L');
			$instance->y += 5;

			if ($client->address != '') {
				$instance->x = 5;
				$instance->SetFont('Arial', 'B', 10);
				$instance->Cell(20, 5, 'Direccion:', $instance->b, 0, 'L');
				$instance->SetFont('Arial', '', 10);
				$instance->Cell(80, 5, $client->address, $instance->b, 0, 'L');
			} 

			if ($client->phone != '') {
				$instance->y += 5;
				$instance->x = 5;
				$instance->SetFont('Arial', 'B', 10);
				$instance->Cell(20, 5, 'Telefono:', $instance->b, 0, 'L');
				$instance->SetFont('Arial', '', 10);
				$instance->Cell(80, 5, $client->phone, $instance->b, 0, 'L');
			} 

			if (!is_null($client->location)) {
				$instance->y += 5;
				$instance->x = 5;
				$instance->SetFont('Arial', 'B', 10);
				$instance->Cell(20, 5, 'Localidad:', $instance->b, 0, 'L');
				$instance->SetFont('Arial', '', 10);
				$instance->Cell(88, 5, $client->location->name, $instance->b, 0, 'L');
			}
		}
	}

	static function currentAcountInfo($instance, $current_acount, $client_id, $compra_actual, $start_y = 32){
		$saldo_anterior = CurrentAcountHelper::getSaldo('client', $client_id, $current_acount);
		$instance->y = $start_y;
		$instance->x = 105;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Saldo anterior:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, '$'.Numbers::price($saldo_anterior), $instance->b, 1, 'L');

		$instance->x = 105;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Compra actual:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, '$'.Numbers::price($compra_actual), $instance->b, 1, 'L');

		$instance->x = 105;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Saldo:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, '$'.Numbers::price($saldo_anterior + $compra_actual), $instance->b, 1, 'L');

		if (!is_null($instance->sale->employee)) {
			$vendedor = $instance->sale->employee->name;
		} else {
			$vendedor = UserHelper::getFullModel()->name;
		}
		$instance->x = 105;
		$instance->SetFont('Arial', 'B', 10);
		$instance->Cell(30, 5, 'Vendedor:', $instance->b, 0, 'L');
		$instance->SetFont('Arial', '', 10);
		$instance->Cell(30, 5, $vendedor, $instance->b, 1, 'L');

		$instance->Line(105, $start_y, 205, $start_y);
		$instance->Line(205, $start_y, 205, $instance->y);
		$instance->Line(205, $instance->y, 105, $instance->y);
		// $instance->Line(105, $instance->y, 105, $start_y);
		// $instance->y += 50;
	}

	static function getWebUrl($url) {
		if (substr($url, 0, 8) == 'https://') {
			return substr($url, 8);
		}
		if (substr($url, 0, 7) == 'http://') {
			return substr($url, 7);
		}
		return $url;
	}
	
}