<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class BudgetPdf extends fpdf {

	function __construct($budget) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->user = UserHelper::getFullModel();
		$this->budget = $budget;

		$this->AddPage();
		$this->articles();
        $this->Output();
        exit;
	}

	function Header() {
		$this->logo();
		$this->budgetNum();
		$this->commerceInfo();
		$this->clientInfo();
		$this->budgetDates();
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

	function budgetNum() {
		$this->SetFont('Arial', 'B', 14);
		$this->x = 105;
		$this->y = 5;

		// Numero
		$this->Cell(100, 10, 'N° '.$this->budget->num, $this->b, 0, 'L');
		$this->y += 10;
		$this->x = 105;
		$this->Cell(100, 10, date_format($this->budget->created_at, 'd/m/Y'), $this->b, 0, 'L');
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

		$this->Cell(100, 5, 'Nombre: '.$this->budget->client->name.' '.$this->budget->client->surname, $this->b, 0, 'L');
		if ($this->budget->client->address != '') {
			$this->y += 5;
			$this->x = 5;
			$this->Cell(100, 5, 'Domicilio: '.$this->budget->client->address, $this->b, 0, 'L');
		} 
		if (!is_null($this->budget->client->iva_condition)) {
			$this->y += 5;
			$this->x = 5;
			$this->Cell(100, 5, 'Con. de IVA: '.$this->budget->client->iva_condition->name, $this->b, 0, 'L');
		}
		if ($this->budget->client->cuit != '') {
			$this->y += 5;
			$this->x = 5;
			$this->Cell(100, 5, 'CUIT: '.$this->budget->client->cuit, $this->b, 0, 'L');
		}
		// $this->lineClient();
	}

	function budgetDates() {
		if (!is_null($this->budget->start_at) && !is_null($this->budget->finish_at)) {
			$this->SetFont('Arial', '', 10);
			$this->x = 105;
			$this->y = 58;
			$this->Cell(100, 5, 'Fecha de entrega', $this->b, 0, 'L');
			$this->y += 5;
			$this->x = 105;
			$date = 'Entre el '.date_format($this->budget->start_at, 'd/m/Y').' y el '.date_format($this->budget->finish_at, 'd/m/Y');
			$this->Cell(100, 5, $date, $this->b, 0, 'L');
			// $this->lineDates();
		}
	}

	function tableHeader() {
		$this->SetFont('Arial', 'B', 12);
		$this->x = 5;
		$this->y = 80;

		$this->Cell(20, 10, 'Codigo', 1, 0, 'C');
		$this->Cell(20, 10, 'Cant.', 1, 0, 'C');
		$this->Cell(80, 10, 'Producto', 1, 0, 'C');
		$this->Cell(30, 10, 'Precio', 1, 0, 'C');
		$this->Cell(20, 10, 'Bonif.', 1, 0, 'C');
		$this->Cell(30, 10, 'Importe', 1, 0, 'C');
	}

	function articles() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 90;

		foreach ($this->budget->articles as $article) {
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

	function printProductDelivered($product) {
		$this->Cell(20, $this->getHeight($product), $product->bar_code, 'T', 0, 'C');
		$this->Cell(20, $this->getHeight($product), $product->amount, 'T', 0, 'C');
		$this->MultiCell(80, $this->line_height, $product->name, 'T', 'C', false);
		$this->x = 125;
		$this->y -= $this->getHeight($product);
		$this->Cell(50, $this->getHeight($product), $this->getTotalDeliveries($product), 'T', 0, 'C');
	}

	function printArticle($article) {
		$this->x = 5;
		$this->Cell(20, $this->line_height, $article->bar_code, 'T', 0, 'C');
		$this->Cell(20, $this->line_height, $article->pivot->amount, 'T', 0, 'C');
		$y_1 = $this->y;
		$this->MultiCell(80, $this->line_height, $article->name, 'T', 'C', false);
		$this->x = 125;

	    $y_2 = $this->y;
		$this->y = $y_1;
		$this->Cell(30, $this->line_height, '$'.Numbers::price($article->pivot->price), 'T', 0, 'C');
		$this->Cell(20, $this->line_height, $this->getBonus($article), 'T', 0, 'C');
		$this->Cell(30, $this->line_height, '$'.Numbers::price(BudgetHelper::totalArticle($article)), 'T', 0, 'C');
		$this->y = $y_2;
	}

	function getDeliveredArticles() {
		$articles = [];
		foreach ($this->budget->articles as $article) {
			if (count($article->pivot->deliveries) >= 1) {
				$articles[] = $article;
			}
		}
		return $products;
	}

	function getTotalDeliveries($product) {
		$total = 0;
		foreach ($product->deliveries as $delivery) {
			$total += $delivery->amount;
		}
		return $total;
	}

	function observations() {
		// $this->SetLineWidth(.2);
		if ($this->budget->observations != '') {
		    $this->x = 5;
	    	$this->SetFont('Arial', 'B', 12);
			$this->Cell(100, 10, 'Observaciones', 'BTL', 0, 'L');
			$this->y += 10;
		    $this->x = 5;
	    	$this->SetFont('Arial', '', 10);
	    	$this->MultiCell(200, $this->line_height, $this->budget->observation, $this->b, 'LTB', false);
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
		$this->Cell(100, 10, 'Total: $'. Numbers::price(BudgetHelper::getTotal($this->budget)), 1, 0, 'R');
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