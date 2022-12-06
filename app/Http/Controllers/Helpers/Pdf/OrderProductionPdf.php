<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class OrderProductionPdf extends fpdf {

	function __construct($order_production) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->user = UserHelper::getFullModel();
		$this->order_production = $order_production;

		$this->AddPage();
		$this->articles();
        $this->Output();
        exit;
	}

	function getFields() {
		return [
			'Codigo'		=> 20,
			'Cant'			=> 20,
			'Producto'		=> 60,
			'Precio'		=> 30,
			'Bonif'			=> 20,
			'Importe'		=> 20,
			'U Entregadas'	=> 30,
		];
	}

	function getModelProps() {
		return [
			[
				'text' 	=> 'Cliente',
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
			[
				'text' 	=> 'Cuit',
				'key'	=> 'cuit',
			],
		];
	}

	function Header() {
		$data = [
			'num' 			=> $this->order_production->num,
			'date'			=> $this->order_production->created_at,
			'title' 		=> 'Orden de produccion',
			'model_info'	=> $this->order_production->client,
			'model_props' 	=> $this->getModelProps(),
			'fields' 		=> $this->getFields(),
		];
		PdfHelper::header($this, $data);
	}


	function Footer() {
		$this->SetLineWidth(.4);
		$this->observations();
		$this->total();
		PdfHelper::comerciocityInfo($this, $this->y);
	}

	function articles() {
		$this->SetFont('Arial', '', 10);
		foreach ($this->order_production->articles as $article) {
			$this->x = 5;
			if ($this->y > 280) {
				$this->AddPage();
			} 
			$this->printArticle($article);
		}
	}

	function printArticle($article) {
		$this->x = 5;
		$this->Cell(20, $this->line_height, $article->bar_code, 'T', 0, 'L');
		$this->Cell(20, $this->line_height, $article->pivot->amount, 'T', 0, 'L');
		$y_1 = $this->y;
		$this->MultiCell(60, $this->line_height, $article->name, 'T', 'L', false);
		$this->x = 105;

	    $y_2 = $this->y;
		$this->y = $y_1;
		$this->Cell(30, $this->line_height, '$'.Numbers::price($article->pivot->price), 'T', 0, 'L');
		$this->Cell(20, $this->line_height, $this->getBonus($article), 'T', 0, 'L');
		$this->Cell(20, $this->line_height, '$'.Numbers::price(BudgetHelper::totalArticle($article)), 'T', 0, 'L');
		$this->Cell(30, $this->line_height, $article->pivot->delivered, 'T', 0, 'L');
		$this->y = $y_2;
	}

	function observations() {
		// $this->SetLineWidth(.2);
		if ($this->order_production->observations != '') {
		    $this->x = 5;
	    	$this->SetFont('Arial', 'B', 12);
			$this->Cell(100, 10, 'Observaciones', 'BTL', 0, 'L');
			$this->y += 10;
		    $this->x = 5;
	    	$this->SetFont('Arial', '', 10);
	    	$this->MultiCell(200, $this->line_height, $this->order_production->observation, $this->b, 'LTB', false);
	    	$this->x = 5;
		}
	}

	function getBonus($article) {
		if (!is_null($article->pivot->bonus)) {
			return $article->pivot->bonus.'%';
		}
		return '';
	}

	function total() {
	    $this->x = 105;
	    $this->SetFont('Arial', 'B', 14);
		$this->Cell(100, 10, 'Total: $'. Numbers::price(BudgetHelper::getTotal($this->order_production)), 0, 1, 'R');
	}

	function comerciocityInfo() {
	    $this->y = 290;
	    $this->x = 5;
	    $this->SetFont('Arial', '', 10);
		$this->Cell(200, 5, 'Comprobante creado con el sistema de control de stock ComercioCity - comerciocity.com', $this->b, 0, 'C');
	}

	function getHeight($product) {
    	$lines = 1;
    	$letras = strlen($product->name);
    	while ($letras > 41) {
    		$lines++;
    		$letras -= 41;
    	}
    	return $this->line_height * $lines;
	}

}