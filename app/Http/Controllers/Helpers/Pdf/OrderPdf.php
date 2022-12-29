<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\Pdf\PdfHelper;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class OrderPdf extends fpdf {

	function __construct($model) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->user = UserHelper::getFullModel();
		$this->model = $model;

		$this->AddPage();
		$this->print();
        $this->Output();
        exit;
	}

	function getFields() {
		return [
			'Nombre' 	=> 110,
			'Precio' 	=> 30,
			'Cantidad'  => 30,
			'Total' 	=> 30,
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
				'text' 	=> 'Direccion',
				'key'	=> 'address',
			],
		];
	}

	function Header() {
		$data = [
			'num' 				=> $this->model->num,
			'date'				=> $this->model->created_at,
			'title' 			=> 'Pedido Online',
			'model_info'		=> !is_null($this->model->buyer->client) ? $this->model->buyer->client : $this->model->buyer,
			'model_props' 		=> $this->getModelProps(),
			'fields' 			=> $this->getFields(),
		];
		PdfHelper::header($this, $data);
	}

	function Footer() {
		PdfHelper::comerciocityInfo($this, $this->y);
	}

	function print() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		foreach ($this->model->articles as $article) {
			if ($this->y < 210) {
				$this->printModel($article);
			} else {
				$this->AddPage();
				$this->x = 5;
				$this->printModel($article);
			}
		}
	}

	function printModel($model) {
		$this->x = 5;
		$y_1 = $this->y;
		$this->MultiCell($this->getFields()['Nombre'], $this->line_height, $model->name, $this->b, 'L', false);
	    $y_2 = $this->y;
		$this->x = $this->getFields()['Nombre']+5;
		$this->y = $y_1;

		$this->Cell($this->getFields()['Precio'], $this->line_height, '$'.Numbers::price($model->pivot->price), $this->b, 0, 'L');
		$this->Cell($this->getFields()['Cantidad'], $this->line_height, $model->pivot->amount, $this->b, 0, 'L');

		$this->Cell($this->getFields()['Total'], $this->line_height, '$'.Numbers::price($model->pivot->price * $model->pivot->amount), $this->b, 0, 'L');
		$this->y = $y_2;

		$this->Line(5, $this->y, 205, $this->y);
	}

}