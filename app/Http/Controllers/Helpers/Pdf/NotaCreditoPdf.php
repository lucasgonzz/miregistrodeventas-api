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
		$this->total = 0;
		
		$this->model = $model;
		$this->setFields();
		$this->AddPage();
		$this->printItems();
        $this->Output();
        exit;
	}

	function setFields() {
		$this->fields = [
			'Codigo' 	=> 40, 
			'Producto' 	=> 90, 
			'Precio' 	=> 20, 
			'Cant' 		=> 20,
			'Total' 	=> 30,
		];
	}

	function Header() {
		PdfHelper::logo($this);
		PdfHelper::numeroFecha($this, '', $this->model->created_at);
		PdfHelper::title($this, 'Nota de Credito');
		PdfHelper::commerceInfo($this);
		PdfHelper::clientInfo($this, $this->model->client);
		PdfHelper::tableHeader($this, $this->fields);
		// PdfHelper::comerciocityInfo($this, 90);
	}

	function printItems() {
		$this->x = 5;
		$this->SetFont('Arial', '', 8);
		foreach ($this->model->articles as $article) {
			$this->Cell($this->fields['Codigo'], 5, $article->bar_code, $this->b, 0, 'C');
			$this->Cell($this->fields['Producto'], 5, $article->name, $this->b, 0, 'C');
			$this->Cell($this->fields['Precio'], 5, '$'.Numbers::price($article->pivot->price), $this->b, 0, 'C');
			$this->Cell($this->fields['Cant'], 5, $article->pivot->amount, $this->b, 0, 'C');
			$this->Cell($this->fields['Total'], 5, $this->getTotal($article), $this->b, 0, 'C');
			$this->y += 5;
			$this->x = 5;
			$this->Line(5, $this->y, 205, $this->y);
		}
	}

	function getTotal($article) {
		$total = (float)$article->pivot->amount * $article->pivot->price;
		$this->total += $total;
		return '$'.Numbers::price($total);
	}

	function Footer() {
		PdfHelper::total($this, $this->total);
		PdfHelper::comerciocityInfo($this, $this->y);
	}

}