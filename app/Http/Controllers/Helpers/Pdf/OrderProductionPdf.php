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

	function Header() {
		$this->logo();
		$this->num();
		$this->commerceInfo();
		$this->clientInfo();
		$this->dates();
		$this->tableHeader();
	}

	function Footer() {
		$y = 230;
		$this->SetLineWidth(.4);
		// $this->Line(5, $y, 205, $y);
		$this->y = $y;
		$this->observations();
		$this->y = $y;
		$this->total();
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
		$this->y = 30;
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
		$this->Cell(100, 10, 'N° '.$this->order_production->num, $this->b, 0, 'L');
		$this->y += 10;
		$this->x = 105;
		$this->Cell(100, 10, date_format($this->order_production->created_at, 'd/m/Y'), $this->b, 0, 'L');
	}

	function commerceInfo() {
		$this->SetFont('Arial', '', 10);
		$this->x = 105;
		$this->y = 35;
		$this->Cell(100, 5, 'Cuit: '.$this->user->afip_information->cuit, $this->b, 0, 'L');
		$this->x = 105;
		$this->y += 5;
		$this->Cell(100, 5, 'Ingresos Brutos: '.$this->user->afip_information->ingresos_brutos, $this->b, 0, 'L');
		$this->x = 105;
		$this->y += 5;
		$this->Cell(100, 5, 'Inicio Actividades: '.date_format($this->user->afip_information->inicio_actividades, 'd/m/Y'), $this->b, 0, 'L');
		// $this->lineCommerce();
	}

	function clientInfo() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 58;

		$this->Cell(100, 5, 'Nombre: '.$this->order_production->client->name.' '.$this->order_production->client->surname, $this->b, 0, 'L');
		if ($this->order_production->client->address != '') {
			$this->y += 5;
			$this->x = 5;
			$this->Cell(100, 5, 'Domicilio: '.$this->order_production->client->address, $this->b, 0, 'L');
		} 
		if (!is_null($this->order_production->client->iva_condition)) {
			$this->y += 5;
			$this->x = 5;
			$this->Cell(100, 5, 'Con. de IVA: '.$this->order_production->client->iva_condition->name, $this->b, 0, 'L');
		}
		if ($this->order_production->client->cuit != '') {
			$this->y += 5;
			$this->x = 5;
			$this->Cell(100, 5, 'CUIT: '.$this->order_production->client->cuit, $this->b, 0, 'L');
		}
		// $this->lineClient();
	}

	function dates() {
		if (!is_null($this->order_production->start_at) && !is_null($this->order_production->finish_at)) {
			$this->SetFont('Arial', '', 10);
			$this->x = 105;
			$this->y = 58;
			$this->Cell(100, 5, 'Fecha de entrega', $this->b, 0, 'L');
			$this->y += 5;
			$this->x = 105;
			$date = 'Entre el '.date_format($this->order_production->start_at, 'd/m/Y').' y el '.date_format($this->order_production->finish_at, 'd/m/Y');
			$this->Cell(100, 5, $date, $this->b, 0, 'L');
			// $this->lineDates();
		}
	}

	function tableHeader() {
		$this->SetFont('Arial', 'B', 12);
		$this->x = 5;
		$this->y = 80;

		$this->SetLineWidth(.4);

		$this->Cell(20, 10, 'Codigo', 1, 0, 'C');
		$this->Cell(20, 10, 'Cant.', 1, 0, 'C');
		$this->Cell(60, 10, 'Producto', 1, 0, 'C');
		$this->Cell(30, 10, 'Precio', 1, 0, 'C');
		$this->Cell(20, 10, 'Bonif.', 1, 0, 'C');
		$this->Cell(20, 10, 'Importe', 1, 0, 'C');
		$this->Cell(30, 10, 'U Entregadas', 1, 0, 'C');
	}

	function articles() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 90;

		foreach ($this->order_production->articles as $article) {
			if ($this->y < 210) {
				$this->printArticle($article);
			} else {
				$this->AddPage();
				$this->x = 5;
				$this->y = 90;
				$this->printArticle($article);
			}
		}
	}

	function printArticle($article) {
		$this->x = 5;
		$this->Cell(20, $this->line_height, $article->bar_code, 'T', 0, 'C');
		$this->Cell(20, $this->line_height, $article->pivot->amount, 'T', 0, 'C');
		$y_1 = $this->y;
		$this->MultiCell(60, $this->line_height, $article->name, 'T', 'C', false);
		$this->x = 105;

	    $y_2 = $this->y;
		$this->y = $y_1;
		$this->Cell(30, $this->line_height, '$'.Numbers::price($article->pivot->price), 'T', 0, 'C');
		$this->Cell(20, $this->line_height, $this->getBonus($article), 'T', 0, 'C');
		$this->Cell(20, $this->line_height, '$'.Numbers::price(BudgetHelper::totalArticle($article)), 'T', 0, 'C');
		$this->Cell(30, $this->line_height, $article->pivot->delivered, 'T', 0, 'C');
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
		$this->Cell(100, 10, 'Total: $'. Numbers::price(BudgetHelper::getTotal($this->order_production)), 1, 0, 'R');
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