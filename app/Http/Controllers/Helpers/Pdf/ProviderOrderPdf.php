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
			'Producto'		=> 80,
			'Notas'			=> 30,
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
		PdfHelper::comerciocityInfo($this, $this->y);
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
		foreach ($this->provider_order->articles as $article) {
			$this->x = 5;
			if ($this->y > 280) {
				$this->AddPage();
			}
			$this->printArticle($article);
		}
	}

	function printArticle($article) {
		$fields = $this->getFields();
		$this->Cell($fields['Codigo'], $this->line_height, $article->bar_code, 0, 0, 'C');
		$this->Cell($fields['Cant'], $this->line_height, $article->pivot->amount, 0, 0, 'C');
		
		$y_1 = $this->y;
		$this->MultiCell($fields['Producto'], $this->line_height, $article->name, 0, 'C', false);
		$y_2 = $this->y;
		$this->y = $y_1;
		$this->x = 5 + $fields['Codigo'] + $fields['Cant'] +  $fields['Producto'];
		
		$y_1 = $this->y;
		$this->MultiCell($fields['Notas'], $this->line_height, $article->pivot->notes, 0, 'C', false);
		if ($this->y > $y_2) {
			$y_2 = $this->y;
		}
		$this->y = $y_1;
		$this->x = 5 + $fields['Codigo'] + $fields['Cant'] +  $fields['Producto'] + $fields['Notas'];

		$this->Cell($fields['Recibidos'], $this->line_height, $article->pivot->received, 0, 0, 'C');
		$this->y = $y_2;
		$this->Line(5, $this->y, 205, $this->y);
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