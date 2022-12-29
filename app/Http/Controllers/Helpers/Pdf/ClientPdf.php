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

class ClientPdf extends fpdf {

	function __construct($clients) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->b = 0;
		$this->line_height = 7;
		
		$this->user = UserHelper::getFullModel();
		$this->clients = $clients;

		$this->AddPage();
		$this->print();
        $this->Output();
        exit;
	}

	function getFields() {
		return [
			'Nombre' 	=> 60,
			'Saldo' 	=> 30,
			'Localidad' => 30,
			'Direccion' => 60,
			'Obs' 		=> 20,
		];
	}

	function Header() {
		$data = [
			// 'num' 				=> $this->budget->num,
			// 'date'				=> $this->budget->created_at,
			'title' 			=> 'Clientes',
			// 'model_info'		=> $this->budget->client,
			// 'model_props' 		=> $this->getModelProps(),
			'fields' 			=> $this->getFields(),
		];
		PdfHelper::header($this, $data);
	}

	function Footer() {
		// PdfHelper::comerciocityInfo($this, $this->y);
	}

	function print() {
		$this->SetFont('Arial', '', 10);
		$this->x = 5;
		foreach ($this->clients as $client) {
			if ($this->y < 210) {
				$this->printClient($client);
			} else {
				$this->AddPage();
				$this->x = 5;
				$this->y = 90;
				$this->printClient($client);
			}
		}
	}

	function printClient($client) {
		$this->x = 5;
		$y_1 = $this->y;
		$this->MultiCell($this->getFields()['Nombre'], $this->line_height, $client->name, $this->b, 'L', false);
	    $y_2 = $this->y;
		$this->x = $this->getFields()['Nombre']+5;
		$this->y = $y_1;

		$this->Cell($this->getFields()['Saldo'], $this->line_height, $client->saldo, $this->b, 0, 'L');
		$this->Cell($this->getFields()['Localidad'], $this->line_height, GeneralHelper::getRelation($client, 'location'), $this->b, 0, 'L');

		$this->MultiCell($this->getFields()['Direccion'], $this->line_height, $client->address, $this->b, 'L', false);
		$this->x = 5 + $this->getFields()['Nombre']+$this->getFields()['Saldo']+$this->getFields()['Localidad']+$this->getFields()['Direccion'];
		if ($this->y > $y_2) {
			$y_2 = $this->y;
		}
		$this->y = $y_1;

		$this->Cell($this->getFields()['Obs'], $this->line_height, $client->observations, $this->b, 0, 'L');
		$this->y = $y_2;

		$this->Line(5, $this->y, 205, $this->y);
	}

}