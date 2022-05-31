<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class BudgetPdf extends fpdf {

	function __construct($budget) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->user = Auth()->user();
		$this->budget = $budget;

		$this->AddPage();
		$this->products();
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
		$y = 220;
		$this->SetLineWidth(.4);
		$this->Line(5, $y, 205, $y);
		$this->y = $y;
		$this->observations();
		$this->y = $y;
		$this->total();
		$this->comerciocityInfo();
	}

	function logo() {
        // Logo
        if (!is_null($this->user->image_url)) {
        	$this->Image(ImageHelper::image($this->user), 0, 0, 0, 30);
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
	    $this->lineInfo();
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
		$this->Cell(100, 10, 'N° '.$this->getNum(), $this->b, 0, 'L');
		$this->y += 10;
		$this->x = 105;
		$this->Cell(100, 10, date_format($this->budget->created_at, 'd/m/Y'), $this->b, 0, 'L');
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
		$this->lineCommerce();
	}

	function clientInfo() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 55;

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
		$this->lineClient();
	}

	function budgetDates() {
		if (!is_null($this->budget->start_at) && !is_null($this->budget->finish_at)) {
			$this->SetFont('Arial', '', 10);
			$this->x = 105;
			$this->y = 55;
			$this->Cell(100, 5, 'Fecha de entrega', $this->b, 0, 'L');
			$this->y += 5;
			$this->x = 105;
			$date = 'Entre el '.date_format($this->budget->start_at, 'd/m/Y').' y el '.date_format($this->budget->finish_at, 'd/m/Y');
			$this->Cell(100, 5, $date, $this->b, 0, 'L');
			$this->lineDates();
		}
	}

	function tableHeader() {
		$this->SetFont('Arial', 'B', 12);
		$this->x = 5;
		$this->y = 80;

		$this->SetLineWidth(.4);
		$this->Cell(20, 10, 'Codigo', 1, 0, 'C');
		$this->Cell(20, 10, 'Cant.', 1, 0, 'C');
		$this->Cell(80, 10, 'Producto', 1, 0, 'C');
		$this->Cell(30, 10, 'Precio', 1, 0, 'C');
		$this->Cell(20, 10, 'Bonif.', 1, 0, 'C');
		$this->Cell(30, 10, 'Importe', 1, 0, 'C');
	}

	function products() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		$this->y = 90;
		foreach ($this->budget->products as $product) {
			if ($this->y < 210) {
				$this->printProduct($product);
			} else {
				$this->AddPage();
				$this->x = 5;
				$this->y = 90;
				$this->printProduct($product);
			}
			$this->x = 5;
			$this->y += $this->getHeight($product);
		}
	}

	function printProduct($product) {
		$this->Cell(20, $this->getHeight($product), $product->id, 'T', 0, 'C');
		$this->Cell(20, $this->getHeight($product), $product->amount, 'T', 0, 'C');
		$this->MultiCell(80, $this->line_height, $product->name, 'T', 'C', false);
		$this->x = 125;
		$this->y -= $this->getHeight($product);
		$this->Cell(30, $this->getHeight($product), '$'.Numbers::price($product->price), 'T', 0, 'C');
		$this->Cell(20, $this->getHeight($product), $product->bonus, 'T', 0, 'C');
		$this->Cell(30, $this->getHeight($product), '$'.Numbers::price($product->price * $product->amount), 'T', 0, 'C');
	}

	function observations() {
		// $this->SetLineWidth(.2);
		if (count($this->budget->observations)) {
		    $this->x = 5;
	    	$this->SetFont('Arial', 'B', 12);
			$this->Cell(100, 10, 'Observaciones', 'BRL', 0, 'L');
			$this->y += 10;
		    $this->x = 5;
	    	$this->SetFont('Arial', '', 10);
			foreach ($this->budget->observations as $observation) {
		    	$this->MultiCell(200, $this->line_height, $observation->text, $this->b, 'L', false);
		    	$this->x = 5;
			}
		}
	}

	function total() {
	    $this->x = 105;
	    $this->SetFont('Arial', 'B', 14);
		$this->Cell(100, 10, 'Total: $'. Numbers::price($this->getTotal()), 'BR', 0, 'R');
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
			$total += $product->price * $product->amount;			
		}
		return $total;
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

	function getNum() {
		$letras_faltantes = 8 - strlen($this->budget->num);
		$cbte_numero = '';
		for ($i=0; $i < $letras_faltantes; $i++) { 
			$cbte_numero .= '0'; 
		}
		$cbte_numero  .= $this->budget->num;
		return $cbte_numero;
	}

}