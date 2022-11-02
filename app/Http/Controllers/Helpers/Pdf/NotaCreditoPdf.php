<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Pdf\PdfHelper;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class NotaCreditoPdf extends fpdf {

	function __construct($model) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->model = $model;

		$this->setFields();
		$this->AddPage();
		$this->printItems();
        $this->Output();
        exit;
	}

	function setFields() {
		$this->fields = [
			[
				'text'  => 'Codigo',
				'width' => 40,
			],
			[
				'text'  => 'Cant',
				'width' => 20,
			],
			[
				'text'  => 'Producto',
				'width' => 110,
			],
			[
				'text'  => 'Total',
				'width' => 30,
			],
		];
	}

	function Header() {
		PdfHelper::logo($this);
		PdfHelper::numeroFecha($this, '', $this->model->created_at);
		PdfHelper::title($this, 'Nota de Credito');
		PdfHelper::commerceInfo($this);
		PdfHelper::tableHeader($this, $this->fields);
		// PdfHelper::comerciocityInfo($this, 90);
	}

	function printItems() {
		$this->x = 5;
		$this->y = 50;
		foreach ($this->model->articles as $key => $value) {
			// code...
		}
		$this->SetFont('Arial', 'B', 11);
		$this->Cell(200, 7, 'Recibimos de '.$this->model->client->name, $this->b, 1, 'L');
		$this->x = 5;
		$this->Cell(200, 7, 'la cantidad de pesos '.$this->model->haber, $this->b, 1, 'L');
	}

	function firma() {
		$this->x = 75;
		$this->y += 10;
		$this->SetFont('Arial', '', 11);
		$this->Cell(50, 7, 'Firma', 'T', 0, 'C');
	}

	function pesos() {
		$this->x = 155;
		$this->y -= 5;
		$this->SetFont('Arial', '', 11);
		$this->Cell(50, 7, 'Son $'.$this->model->haber, 1, 0, 'L');
	}

	function Footer() {
		PdfHelper::comerciocityInfo($this);
	}

}