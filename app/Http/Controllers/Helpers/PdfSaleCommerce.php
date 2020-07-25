<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Sale;
use App\Client;
use App\Http\Controllers\Helpers\PdfArticleHelper;

require(__DIR__.'/../fpdf/fpdf.php');
use fpdf;

class PdfSaleCommerce extends fpdf {

	function __construct($sale_id, $company_name, $borders) {
		parent::__construct();
		$sale = Sale::find($sale_id);
		$this->sale = $sale;
		$this->client = $sale->client;
		$this->company_name = $company_name;
		if ($borders) {
        	$this->borders = 1;
        } else {
        	$this->borders = 'B';
        }
        $this->num_page = 0;
        $this->total_pages = 1;
		$this->articulos_en_esta_pagina = 0;
        $this->suma_costos_pagina = 0;
        $this->suma_precios_pagina = 0;
	}

	function printSale() {
		$this->AddPage();
        $this->SetFont('Arial', '', 12);
		$articles = $this->sale->articles;
		$articulos_por_pagina = 15;
		$articulos_en_esta_venta = 0;
        $suma_costos_venta = 0;
        $suma_precios_venta = 0;
		$cantidad_articulos = count($articles);

		$count = 0;
		for ($i=0; $i < $cantidad_articulos; $i++) { 
			$count++;
			if ($count == $articulos_por_pagina) {
				$this->total_pages++;
				$count = 0;
			}
		}

		$this->__Header();
		foreach ($articles as $article) {
			if ($this->articulos_en_esta_pagina < $articulos_por_pagina && $articulos_en_esta_venta < $cantidad_articulos) {
				$this->suma_costos_pagina += $article->cost * $article->pivot->amount;
				$this->suma_precios_pagina += $article->price * $article->pivot->amount;
				$this->articulos_en_esta_pagina++;
				$articulos_en_esta_venta++;
				$this->printArticle($article);
			} else {
				$this->printInfo();
        		if ($articulos_en_esta_venta < $cantidad_articulos) {
                    $suma_costos_venta += $this->suma_costos_pagina;
                    $suma_precios_venta += $this->suma_precios_pagina;
                    $this->suma_costos_pagina = 0;
                    $this->suma_precios_pagina = 0;
                    $this->articulos_en_esta_pagina = 0;
                    $this->suma_costos_pagina += $article->cost * $article->pivot->amount;
                    $this->suma_precios_pagina += $article->price * $article->pivot->amount;
                    $this->articulos_en_esta_pagina++;
                    $articulos_en_esta_venta++;
                	$this->AddPage();
                	$this->__Header();
                	// $this->SetArticleConf();
                    $this->printArticle($article);
        		}
            }	# Termina if 
        }	# Termina for

        // Se terminaron los articulos de la venta
        $suma_costos_venta += $this->suma_costos_pagina;
        $suma_precios_venta += $this->suma_precios_pagina;
		$this->printInfo();
        
        $this->Ln();
        $this->SetX(5);
        $this->Cell(100,7,'Costos de esta venta: $'.$this->price($suma_costos_venta),0,0,'L');
        $this->Cell(100,7,'Precios de esta venta: $'.$this->price($suma_precios_venta),0,0,'L');
        $this->Ln();
        $this->SetX(5);
        $this->Output();
        exit;
	}

	function printInfo() {
		$this->num_page++;
    	$this->SetXY(5, -50);
			$this->Cell(100,7,'Página '.$this->num_page.' de '.$this->total_pages,0,0,'L');
		$this->Cell(100,7,$this->articulos_en_esta_pagina.' arículos en esta página',0,0,'L');
		$this->Ln();
		$this->SetX(5);
		$this->Cell(100,7,'Costos de esta página: $'.$this->price($this->suma_costos_pagina),0,0,'L');
		$this->Cell(100,7,'Precios de esta página: $'.$this->price($this->suma_precios_pagina),0,0,'L');
	}

	function printArticle($article) {
		$this->SetX(5);
		$this->SetDrawColor(51,51,51);
		$this->SetLineWidth(.4);
		$this->SetFont('Arial', '', 11);
		
		$this->Cell(45,10,$article->bar_code,$this->borders,0,'L');
    	$name = $article->name;
    	if (strlen($name) > 20) {
    		$name = substr($article->name, 0, 20) . ' ..';
    	}
    	$this->Cell(45,10,$name,$this->borders,0,'L');
    	$this->Cell(20,10,'$'.$this->price($article->cost),$this->borders,0,'L');
    	$this->Cell(20,10,'$'.$this->price($article->price),$this->borders,0,'L');
    	// $this->Cell(20,10,$article->pivot->amount,$this->borders,0,'L');
    	$this->Cell(20,10,PdfArticleHelper::amount($article),$this->borders,0,'L');
    	$this->Cell(25,10,'$'.$this->price(PdfArticleHelper::getSubTotalCost($article)),$this->borders,0,'L');
    	$this->Cell(25,10,'$'.$this->price(PdfArticleHelper::getSubTotalPrice($article)),$this->borders,0,'L');
    	$this->Ln();
	}
	
	function price($price) {
		$pos = strpos($price, '.');
		if ($pos != false) {
			$centavos = explode('.', $price)[1];
			$new_price = explode('.', $price)[0];
			if ($centavos != '00') {
				$new_price += ".$centavos";
				return number_format($new_price, 2, ',', '.');
			} else {
				return number_format($new_price, 0, '', '.');			
			}
		} else {
			return number_format($price, 0, '', '.');
		}
	}

	function getTotalUnidades() {
		$total_unidades = 0;
		foreach ($this->sale->articles as $article) {
			$total_unidades += $article->pivot->amount;
		}
		return $total_unidades;
	}

	function __Header() {
		$user = Auth()->user();
		$this->SetXY(10, 10);

		// Si el nombre del negocio es verdadero se escribe
		if ($this->company_name) {
			$this->SetFont('Arial', 'B', 18, 'C');
			$this->Cell(100,5,Auth()->user()->company_name,0,0,'L');
			// $this->SetY(35);
		}
		
		$client = $this->sale->client;

		// Se escribe la fecha
		$this->SetXY(100, 10);
		$this->SetFont('Arial', '', 11, 'C');
		$this->Cell(50,5,'Venta n°: '.$this->sale->num_sale,0,0,'L');
		$this->Cell(50,5,'Cliente: '.$client->name,0,0,'L');
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Fecha: '.date_format($this->sale->created_at, 'd/m/y'),0,0,'L');
		$this->Cell(50,5,'Hora: '.date_format($this->sale->created_at, 'H:m'),0,0,'L');
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Artículos vendidos: '.count($this->sale->articles),0,0,'L');
		$this->Cell(50,5,'Unidades vendidas: '.$this->getTotalUnidades(),0,0,'L');
		$this->SetLineWidth(.6);
		$this->Line(5,27,205,27);

		// Se baja 1cm abajo
		$this->SetY(35);

		$this->SetX(5);
		$this->SetFont('Arial', 'B', 14, 'L');
		$this->Cell(45, 5, 'Codigo', 0, 0, 'L');
		$this->Cell(45, 5, 'Artículo', 0, 0, 'L');
		$this->Cell(20, 5, 'Costo', 0, 0, 'L');
		$this->Cell(20, 5, 'Precio', 0, 0, 'L');
		$this->Cell(20, 5, 'Cant.', 0, 0, 'L');
		$this->Cell(25, 5, 'Sub Cos', 0, 0, 'L');
		$this->Cell(25, 5, 'Sub Pre', 0, 0, 'L');
		$this->SetLineWidth(.8);
		// $this->SetDrawColor(100, 174, 238);
		$this->Line(5, 42, 205, 42);
		$this->SetY(42);
	}

	// function Footer() {
	// 	$this->SetFont('Arial', '', 11);
	// 	$this->AliasNbPages();
	// 	$this->SetY(-20);
	// 	$this->Write(5,'Hoja '.$this->PageNo().'/{nb}');
	// }
}