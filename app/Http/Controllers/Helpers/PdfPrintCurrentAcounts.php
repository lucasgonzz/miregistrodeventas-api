<?php

namespace App\Http\Controllers\Helpers;

use App\Client;
use App\CurrentAcount;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintArticles;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use Carbon\Carbon;
use fpdf;
require(__DIR__.'/../fpdf/fpdf.php');

class PdfPrintCurrentAcounts extends fpdf {

	function __construct($ids = null, $client_id = null, $months_ago = null) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->current_acounts = [];
		$this->ids = $ids;
		$this->client_id = $client_id;
		// $this->client = Client::find($client_id);
		$this->months_ago = $months_ago;
		$this->current_acount_por_pagina = 40;
		$this->current_acount_impresos = 0;
		$this->num_page = 0;
		$this->Y = 42;

		/*
			* Se fijan los valores dependiendo los campos que se quieran mostrar
			para despues sumarlos a todos y calcular los margenes
		*/
		$widths = [];
		$widths['created_at'] = 20;
		$widths['detalle'] = 40;
		$widths['debe'] = 30;
		$widths['haber'] = 30;
		$widths['saldo'] = 40;
		$widths['description'] = 40;
		$this->widths = $widths;

		$this->margins = (210 - array_sum($widths)) / 2;

		$this->initCurrentAcounts();
	}

	function initCurrentAcounts() {
		if (!is_null($this->client_id)) {
			$this->client = Client::find($this->client_id);
	        $this->current_acounts = CurrentAcountHelper::getCurrentAcountsSinceMonths(
	        								$this->client_id, $this->months_ago);
		} else {
			foreach ($this->ids as $current_acount_id) {
				$this->current_acounts[] = CurrentAcount::find($current_acount_id);
			}
			$this->client = Client::find($this->current_acounts[0]->client_id);
		}
		$this->cantidad_current_acounts = count($this->current_acounts);
		$this->current_acounts_en_esta_pagina = 0;
	}

	function printCurrentAcounts() {
		$user = Auth()->user();
        $this->AddPage();
        $this->__Header();
		$this->setTotalPaginas();
		foreach ($this->current_acounts as $current_acount) {
			$this->current_acount = $current_acount;
	        $this->sumarCantidadDeCurrentAcounts();
            $this->printCurrentAcount($current_acount);

            if ($this->current_acounts_en_esta_pagina >= $this->current_acount_por_pagina 
            	|| $this->current_acount_impresos == $this->cantidad_current_acounts) {
                $this->num_page++;
                // $this->printInfoPagina();
	            if ($this->current_acount_impresos < $this->cantidad_current_acounts) {
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
		$this->current_acounts_en_esta_pagina = 0;
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
		for ($i=0; $i < $this->cantidad_current_acounts; $i++) { 
			$count++;
			if ($count > $this->current_acount_por_pagina) {
				$this->total_pages++;
				$count = 0;
			}
		}
	}


	function sumarCantidadDeCurrentAcounts() {
        $this->current_acounts_en_esta_pagina++;
        $this->current_acount_impresos++;
	}

	function setCurrentAcountConf() {
        $this->setFont('Arial', '', 10);
        $this->SetDrawColor(51,51,51);
		$this->SetLineWidth(.4);
        $this->SetY($this->Y);
        $this->SetX($this->margins);
	}
	
	function printCurrentAcount($current_acount) {
		$this->SetCurrentAcountConf();	
		$this->SetLineWidth(.4);
		$this->SetDrawColor(51,51,51);
        $this->Cell($this->widths['created_at'],$this->getHeight(),date_format($current_acount->created_at, 'd/m/Y'),0, 0,'L');
        $this->MultiCell($this->widths['detalle'],6,$current_acount->detalle,0,'L', false);
        $this->SetY($this->Y);
        $this->SetX(65);
        $this->Cell($this->widths['debe'],$this->getHeight(),'$'.Numbers::price($current_acount->debe),0, 0,'R');
        $this->Cell($this->widths['haber'],$this->getHeight(),'$'.Numbers::price($current_acount->haber),0, 0,'R');
        $this->Cell($this->widths['saldo'],$this->getHeight(),'$'.Numbers::price($current_acount->saldo),0, 0,'R');
        $this->MultiCell($this->widths['description'],6,$current_acount->description,0, 'C',0);
        $this->Ln();
		$this->calculateY();
        $this->printLine();
    }

    function calculateY() {
    	$lines = 1;
    	$letras = strlen($this->current_acount->detalle);
    	if ($letras < strlen($this->current_acount->description)) {
    		$letras = strlen($this->current_acount->description);
    	}
    	while ($letras > 19) {
    		$lines++;
    		$letras -= 19;
    		$this->current_acounts_en_esta_pagina++;
    	}
    	$this->Y += 6 * $lines;
    }

    function printLine() {
		$this->SetDrawColor(0,0,0);
		$this->Line($this->margins, $this->Y, $this->margins+array_sum($this->widths), $this->Y);
    }

    function getHeight() {
    	$lines = 1;
    	$letras = strlen($this->current_acount->detalle);
    	if ($letras < strlen($this->current_acount->description)) {
    		$letras = strlen($this->current_acount->description);
    	}
    	while ($letras > 19) {
    		$lines++;
    		$letras -= 19;
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
		$this->Cell(50,5,'Cliente: '.$this->client->name,0,0,'L');
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
		$this->Cell($this->widths['created_at'], 5, 'Fecha', 0, 0, 'C');
		$this->Cell($this->widths['detalle'], 5, 'Detalle', 0, 0, 'C');
		$this->Cell($this->widths['debe'], 5, 'Debe', 0, 0, 'R');
		$this->Cell($this->widths['haber'], 5, 'Haber', 0, 0, 'R');
		$this->Cell($this->widths['saldo'], 5, 'Saldo', 0, 0, 'R');
		$this->Cell($this->widths['description'], 5, 'Descuentos', 0, 0, 'R');

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