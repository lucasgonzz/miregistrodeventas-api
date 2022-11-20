<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Pdf\PdfHelper;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class ProviderOrderPdf extends fpdf {

	function __construct($provider_order) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->provider_order = $provider_order;

		$this->AddPage();
		$this->articles();
        $this->Output();
        exit;
	}

	function getFields() {
		return [
			'Codigo'		=> 40,
			'Cant'			=> 20,
			'Producto'		=> 110,
			'Recibidos'		=> 30,
		];
	}

	function getModelProps() {
		return [
			[
				'text' 	=> 'Proveedor',
				'key'	=> 'name',
			],
			[
				'text' 	=> 'Telefono',
				'key'	=> 'phone',
			],
			[
				'text' 	=> 'Localidad',
				'key'	=> 'location.name',
			],
		];
	}

	function Header() {
		$data = [
			'num' 			=> $this->getNum(),
			'date'			=> $this->provider_order->created_at,
			'title' 		=> 'Pedido a Proveedor',
			'model_info'	=> $this->provider_order->provider,
			'model_props' 	=> $this->getModelProps(),
			'fields' 		=> $this->getFields(),
		];
		PdfHelper::header($this, $data);
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

	function providerInfo() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 50;

		$this->Cell(100, 5, 'Nombre: '.$this->provider_order->provider->name, $this->b, 1, 'L');
		if ($this->provider_order->address != '') {
			$this->x = 5;
			$this->Cell(100, 5, 'Domicilio: '.$this->provider_order->address, $this->b, 1, 'L');
		} 
	}

	function articles() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
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
		}
	}

	function printArticle($article) {
		$this->Cell($this->getFields()['Codigo'], $this->line_height, $article->bar_code, 0, 0, 'C');
		$this->Cell($this->getFields()['Cant'], $this->line_height, $article->pivot->amount, 0, 0, 'C');
		$y_1 = $this->y;
		$this->MultiCell($this->getFields()['Producto'], $this->line_height, $article->name, 0, 'C', false);
		$y_2 = $this->y;
		$this->y = $y_1;
		$this->x = 175;
		$this->Cell($this->getFields()['Recibidos'], $this->line_height, $article->pivot->received, 0, 0, 'C');
		$this->y = $y_2;
		$this->Line(5, $this->y, 205, $this->y);
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