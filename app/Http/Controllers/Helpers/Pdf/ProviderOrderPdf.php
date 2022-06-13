<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class ProviderOrderPdf extends fpdf {

	function __construct($provider_order) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->user = UserHelper::getFullModel();
		$this->provider_order = $provider_order;

		$this->AddPage();
		$this->articles();
        $this->Output();
        exit;
	}

	function Header() {
		$this->logo();
		$this->num();
		$this->commerceInfo();
		$this->providerInfo();
		$this->tableHeader();
	}

	function Footer() {
		$y = 220;
		$this->SetLineWidth(.4);
		// $this->Line(5, $y, 205, $y);
		$this->y = $y;
		// $this->observations();
		$this->y = $y;
		// $this->total();
		$this->comerciocityInfo();
	}

	function logo() {
        // Logo
        if (!is_null($this->user->image_url)) {
        	$this->Image(ImageHelper::image($this->user), 5, 5, 0, 27);
        }
		
        // Company name
		$this->SetFont('Arial', 'B', 10);
		$this->x = 5;
		$this->y = 32;
		$this->Cell(100, 5, $this->user->company_name, $this->b, 0, 'C');

		// Info
		$this->SetFont('Arial', '', 10);
		$address = null;
		if (count($this->user->addresses) >= 1) {
			$address = $this->user->addresses[0];
		}
		$info = $this->user->afip_information->razon_social;
		$info .= ' / '. $this->user->afip_information->iva_condition->name;
		if (!is_null($address)) {
			$info .= ' / '. $address->street.' N° '.$address->street_number;
			$info .= ' / '. $address->city.' / '.$address->province;
		}
		$info .= ' / '. $this->user->phone;
		$info .= ' / '. $this->user->email;
		$info .= ' / '. $this->user->online;
		$this->x = 5;
		$this->y += 5;
	    $this->MultiCell(100, 5, $info, $this->b, 'L', false);
	    // $this->lineInfo();
	}

	function lineInfo() {
		// Left
		$this->Line(5, 37, 5, 52);
		// Right
		$this->Line(102, 37, 102, 52);
		// Top
		$this->Line(5, 37, 102, 37);
		// bottom
		$this->Line(5, 52, 102, 52);
	}

	function lineClient() {
		// Left
		$this->Line(5, 55, 5, 75);
		// Right
		$this->Line(102, 55, 102, 75);
		// Top
		$this->Line(5, 55, 102, 55);
		// bottom
		$this->Line(5, 75, 102, 75);
	}

	function lineCommerce() {
		// Left
		$this->Line(105, 37, 105, 52);
		// Right
		$this->Line(205, 37, 205, 52);
		// Top
		$this->Line(105, 37, 205, 37);
		// bottom
		$this->Line(105, 52, 205, 52);
	}

	function lineDates() {
		// Left
		$this->Line(105, 55, 105, 65);
		// Right
		$this->Line(205, 55, 205, 65);
		// Top
		$this->Line(105, 55, 205, 55);
		// bottom
		$this->Line(105, 65, 205, 65);
	}

	function num() {
		$this->SetFont('Arial', 'B', 14);
		$this->x = 105;
		$this->y = 5;

		// Numero
		$this->Cell(100, 10, 'N° '.$this->getNum(), $this->b, 0, 'L');
		$this->y += 10;
		$this->x = 105;
		$this->Cell(100, 10, date_format($this->provider_order->created_at, 'd/m/Y'), $this->b, 0, 'L');
	}

	function commerceInfo() {
		$this->SetFont('Arial', '', 10);
		$this->x = 105;
		$this->y = 37;
		$this->Cell(100, 5, 'Cuit: '.$this->user->afip_information->cuit, $this->b, 0, 'L');
		$this->x = 105;
		$this->y += 5;
		$this->Cell(100, 5, 'Ingresos Brutos: '.$this->user->afip_information->ingresos_brutos, $this->b, 0, 'L');
		$this->x = 105;
		$this->y += 5;
		$this->Cell(100, 5, 'Inicio Actividades: '.date_format($this->user->afip_information->inicio_actividades, 'd/m/Y'), $this->b, 0, 'L');
		// $this->lineCommerce();
	}

	function providerInfo() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 55;

		$this->Cell(100, 5, 'Nombre: '.$this->provider_order->provider->name, $this->b, 0, 'L');
		if ($this->provider_order->address != '') {
			$this->y += 5;
			$this->x = 5;
			$this->Cell(100, 5, 'Domicilio: '.$this->provider_order->address, $this->b, 0, 'L');
		} 
	}

	function tableHeader() {
		$this->SetFont('Arial', 'B', 12);
		$this->x = 5;
		$this->y = 80;

		$this->SetLineWidth(.4);
		$this->Cell(40, 10, 'Codigo', 1, 0, 'C');
		$this->Cell(20, 10, 'Cant.', 1, 0, 'C');
		$this->Cell(110, 10, 'Producto', 1, 0, 'C');
		$this->Cell(30, 10, 'Recibidos', 1, 0, 'C');
	}

	function articles() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 90;
		foreach ($this->provider_order->articles as $article) {
			if ($this->y < 210) {
				$this->printArticle($article);
			} else {
				$this->AddPage();
				$this->x = 5;
				$this->y = 90;
				$this->printArticle($article);
			}
			$this->x = 5;
			$this->y += $this->getHeight($article);
		}
	}

	function printArticle($article) {
		$this->Cell(40, $this->getHeight($article), $article->bar_code, 'T', 0, 'C');
		$this->Cell(20, $this->getHeight($article), $article->pivot->amount, 'T', 0, 'C');
		$this->MultiCell(110, $this->line_height, $article->name, 'T', 'C', false);
		$this->x = 175;
		$this->y -= $this->getHeight($article);
		$this->Cell(30, $this->getHeight($article), $article->pivot->received, 'T', 0, 'C');
	}

	function observations() {
		// $this->SetLineWidth(.2);
		if (count($this->budget->observations)) {
		    $this->x = 5;
	    	$this->SetFont('Arial', 'B', 12);
			$this->Cell(100, 10, 'Observaciones', 'BTL', 0, 'L');
			$this->y += 10;
		    $this->x = 5;
	    	$this->SetFont('Arial', '', 10);
			foreach ($this->budget->observations as $observation) {
		    	$this->MultiCell(200, $this->line_height, $observation->text, $this->b, 'LTB', false);
		    	$this->x = 5;
			}
		}
	}

	function getBonus($product) {
		if (!is_null($product->bonus)) {
			return $product->bonus.'%';
		}
		return '';
	}

	function totalProduct($product) {
		$total = $product->price * $product->amount;
		if (!is_null($product->bonus)) {
			$total -= $total * (float)$product->bonus / 100;
		}
		return $total;
	}

	function total() {
	    $this->x = 105;
	    $this->SetFont('Arial', 'B', 14);
		$this->Cell(100, 10, 'Total: $'. Numbers::price($this->getTotal()), 1, 0, 'R');
	}

	function comerciocityInfo() {
	    $this->y = 290;
	    $this->x = 5;
	    $this->SetFont('Arial', '', 10);
		$this->Cell(200, 5, 'Comprobante creado con el sistema de control de stock ComercioCity - comerciocity.com', $this->b, 0, 'C');
	}

	function getTotal() {
		$total = 0;
		foreach ($this->budget->products as $product) {
			$total += $this->totalProduct($product);
		}
		return $total;
	}

	function getHeight($article) {
    	$lines = 1;
    	$letras = strlen($article->name);
    	while ($letras > 41) {
    		$lines++;
    		$letras -= 41;
    	}
    	return $this->line_height * $lines;
	}

	function getNum() {
		$letras_faltantes = 8 - strlen($this->provider_order->num);
		$cbte_numero = '';
		for ($i=0; $i < $letras_faltantes; $i++) { 
			$cbte_numero .= '0'; 
		}
		$cbte_numero  .= $this->provider_order->num;
		return $cbte_numero;
	}

}