<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class SaleTicketPdf extends fpdf {

	function __construct($sale, $address) {
		$this->line_height = 7;
		$this->user = UserHelper::getFullModel();
		$this->sale = $sale;
		$this->address = $address;

		parent::__construct('P', 'mm', [58, $this->getPdfHeight()]);
		$this->SetAutoPageBreak(false);
		$this->b = 0;

		$this->AddPage();
		$this->items();
        $this->Output();
        exit;
	}

	function Header() {
		$this->logo();
	}

	function Footer() {
		$this->total();
		$this->thanks();
		$this->date();
		$this->address();
		// $this->comerciocityInfo();
	}

	function logo() {
        // Logo
        if (!is_null($this->user->image_url)) {
        	$this->Image(ImageHelper::image($this->user), 17, 0, 0, 25);
        }
		
        // Company name
		$this->SetFont('Arial', 'B', 14);
		$this->x = 7;
		$this->y = 25;
		$this->Cell(45, 10, $this->user->company_name, $this->b, 0, 'C');
		$this->y += 10;

		// $this->Line(2, 30, 53, 30);
		// Info
	    // $this->lineInfo();
	}

	function items() {
		$this->x = 2;

		foreach ($this->sale->combos as $combo) {
			$this->SetFont('Arial', '', 9);

			$y_1 = $this->y;
			$this->MultiCell(34, $this->line_height, $combo->name.' ('.$combo->pivot->amount.')', 'LTB', 'L', 0);
			$y_2 = $this->y;
			$this->x = 36;
			$this->y = $y_1;
			
			$this->SetFont('Arial', 'B', 9);
			$this->Cell(20, $y_2 - $y_1, '$'.Numbers::Price($combo->pivot->price * $combo->pivot->amount), 'RTB', 0, 'R');

			$this->y = $y_2;
			
			$this->comboArticles($combo);
			$this->x = 2;
		}

		foreach ($this->sale->articles as $article) {
			$this->SetFont('Arial', '', 9);
			$y_1 = $this->y;
			$this->MultiCell(35, $this->line_height, $article->name." ({$article->pivot->amount})", 'TLB', 'L', 0);
			$y_2 = $this->y;

			$this->x = 36;
			$this->y = $y_1;

			$this->SetFont('Arial', 'B', 9);
			$this->Cell(20, $y_2 - $y_1, '$'.Numbers::Price($article->pivot->price * $article->pivot->amount), 'TRB', 0, 'R');
			
			$this->x = 2;
			$this->y = $y_2;
		}
	}

	function comboArticles($combo) {
		$this->SetFont('Arial', '', 9);

		foreach ($combo->articles as $article) {
			$this->x = 6;
			$this->MultiCell(50, $this->line_height, $article->name.' ('.$article->pivot->amount.')', 'LR', 'L', 0);
		}
	}

	function total() {
	    $this->x = 2;
	    $this->SetFont('Arial', 'B', 12);
		$this->Cell(54, 10, 'Total: $'. Numbers::price(SaleHelper::getTotalSale($this->sale)), 1, 0, 'C');
		$this->y += 10;
	}

	function thanks() {
	    $this->x = 2;
	    $this->SetFont('Arial', '', 10);
		$this->Cell(54, 10, 'GRACIAS POR SU VISITA', 0, 0, 'C');
		$this->y += 10;
	}

	function date() {
		$date = date_format($this->sale->created_at, 'd/m/Y H:i');
	    $this->x = 2;
	    $this->SetFont('Arial', '', 9);
		$this->Cell(54, 5, $date, 0, 0, 'L');
		$this->y += 5;
	}
	
	function address() {
		if (!is_null($this->address)) {
			$address = $this->address->street.' '.$this->address->street_number;
		    $this->x = 2;
		    $this->SetFont('Arial', 'I', 9);
			$this->Cell(54, 5, $address, 0, 0, 'L');
		}
	}

	function comerciocityInfo() {
	    $this->y = 290;
	    $this->x = 5;
	    $this->SetFont('Arial', '', 10);
		$this->Cell(200, 5, 'Comprobante creado con el sistema de control de stock ComercioCity - comerciocity.com', $this->b, 0, 'C');
	}

	function getHeight($item, $maximo_letas) {
    	$lines = 1;
    	$letras = strlen($item->name);
    	while ($letras > $maximo_letas) {
    		$lines++;
    		$letras -= $maximo_letas;
    	}
    	return $this->line_height * $lines;
	}

	function getPdfHeight() {
		$height = 85;
		foreach ($this->sale->combos as $combo) {
			$height += $this->getHeight($combo, 20);
			foreach ($combo->articles as $article) {
				$height += $this->getHeight($article, 20);
			}
		}
		foreach ($this->sale->articles as $article) {
			$height += $this->getHeight($article, 20);
		}
		// $height += 
		return $height;
	}

}