<?php

namespace App\Http\Controllers\Helpers;

use App\Client;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintArticles;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use Carbon\Carbon;
use fpdf;
require(__DIR__.'/../fpdf/fpdf.php');

class PdfPrintClients extends fpdf {

	function __construct($seller, $clients) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->seller = $seller;
		$this->clients = $clients;
		$this->cantidad_clients = count($clients);
		$this->clients_por_pagina = 40;
		$this->clients_en_esta_pagina = 0;
		$this->clients_impresos = 0;
		$this->num_page = 0;
		$this->Y = 42;

		/*
			* Se fijan los valores dependiendo los campos que se quieran mostrar
			para despues sumarlos a todos y calcular los margenes
		*/
		$widths = [];
		$widths['name'] = 50;
		$widths['surname'] = 50;
		$widths['address'] = 50;
		$widths['saldo'] = 50;
		$this->widths = $widths;

		$this->margins = (210 - array_sum($widths)) / 2;

	}

	function printClients() {
		$user = Auth()->user();
        $this->AddPage();
        $this->__Header();
		$this->setTotalPaginas();
		foreach ($this->clients as $client) {
			$this->client = $client;
	        $this->sumarCantidadDeClients();
            $this->printClient($client);

            if ($this->clients_en_esta_pagina >= $this->clients_por_pagina 
            	|| $this->clients_impresos == $this->cantidad_clients) {
                $this->num_page++;
                // $this->printInfoPagina();
	            if ($this->clients_impresos < $this->cantidad_clients) {
	            	$this->AddPage();
	            	$this->__Header();
	            }
	            $this->reset();
	        }
    	}
        $this->Output();
        exit;
	}

	function reset() {
		$this->clients_en_esta_pagina = 0;
		$this->Y = 42;
	}

	function printInfoPagina() {
		$this->SetY(265);
        $this->SetX(5);
        $this->SetFont('Arial', '', 10);
        $this->Cell(100,5,'Página '.$this->num_page.' de '.$this->total_pages,0,0,'L');
	}

	function setTotalPaginas() {
		$count = 0;
		$this->total_pages = 1;
		for ($i=0; $i < $this->cantidad_clients; $i++) { 
			$count++;
			if ($count > $this->clients_por_pagina) {
				$this->total_pages++;
				$count = 0;
			}
		}
	}


	function sumarCantidadDeClients() {
        $this->clients_en_esta_pagina++;
        $this->clients_impresos++;
	}

	function setCurrentAcountConf() {
        $this->setFont('Arial', '', 10);
		$this->SetLineWidth(.4);
		$this->SetDrawColor(51,51,51);
	}
	
	function printClient() {
		$this->SetCurrentAcountConf();	
        // $this->Cell($this->widths['created_at'],6,$this->clients_impresos.' id: '.$client->id , 0, 0,'L');
        $this->SetY($this->Y);
        $this->SetX($this->margins);
        $this->Cell($this->widths['name'],$this->getHeight(),$this->client->name, 'B', 0,'L');
        $name = $this->client->name;
        $this->Cell($this->widths['surname'],6,$this->client->surname, 'B',0,'C', false);
        $this->Cell($this->widths['address'],$this->getHeight(),$this->client->address, 'B', 0,'C');
        $this->Cell($this->widths['saldo'],$this->getHeight(),'$'.Numbers::price($this->client->saldo), 'B', 0,'R');
        $this->Ln();
		$this->calculateY();
    }

    function calculateY() {
    	// $this->Y += 6;
    	// return;
    	$lines = 1;
    	$letras = strlen($this->client->detalle);
    	while ($letras > 22) {
    		// dd($letras);
    		$lines++;
    		$letras -= 22;
    		$this->clients_en_esta_pagina++;
    	}
    	$this->Y += 6 * $lines;
    }

    function getHeight() {
    	$lines = 1;
    	$letras = strlen($this->client->detalle);
    	while ($letras > 22) {
    		$lines++;
    		$letras -= 22;
    	}
    	return 6 * $lines;
    }

	function __Header() {

		$user = Auth()->user();
		$this->SetXY(10, 10);

		// Si el nombre del negocio es verdaderop se escribe
		// y se pone el cursor 1cm mas abajo
		$this->SetFont('Arial', 'B', 18, 'L');
		$this->Cell(100,5,Auth()->user()->company_name,0,0,'L');
		$this->SetY(35);

		// Se escribe la fecha
		$this->SetXY(100, 10);
		$this->SetFont('Arial', '', 11, 'L');
		$this->Cell(50,5,'Vendedor: '.$this->seller->name.' '.$this->seller->surname,0,0,'L');
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Fecha: '.date_format(Carbon::now(), 'd/m/y'),0,0,'L');
		$this->Cell(50,5,'Hora: '.date_format(Carbon::now(), 'H:m'),0,0,'L');
		$this->Ln();
		$this->SetLineWidth(.6);
		$this->Line(5,27,205,27);

		// Se baja 1cm abajo
		$this->SetY(32);

		// Se empieza a escribir la cabecera
		$this->SetX($this->margins);
		$this->SetFont('Arial', 'B', 14, 'L');
		$this->Cell($this->widths['name'], 5, 'Nombre', 0, 0, 'C');
		$this->Cell($this->widths['surname'], 5, 'Apellido', 0, 0, 'C');
		$this->Cell($this->widths['address'], 5, 'Direccion', 0, 0, 'C');
		$this->Cell($this->widths['saldo'], 5, 'Saldo', 0, 0, 'C');

		// Se dibuja la linea celeste que separa el thead del tbody
		$this->SetLineWidth(.6);
		// $this->SetDrawColor(100, 174, 238);
		$this->Line($this->margins, 40, $this->margins+array_sum($this->widths), 40);

		$this->SetY(42);
	}

	function __Footer() {
		$this->SetFont('Arial', '', 11);
		$this->AliasNbPages();
		$this->SetY(-30);
		// $this->Write(5,'Hoja '.$this->num_PageNo().'/{nb}');
	}
}