<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Sale;
use App\Client;
use App\Http\Controllers\Helpers\PdfPrintArticles;

require(__DIR__.'/../fpdf/fpdf.php');
use fpdf;

class PdfPrintSale extends fpdf {

	function __construct($sales_id, $company_name, $articles_cost, $articles_subtotal_cost, $articles_total_price, $articles_total_cost, $borders) {
		parent::__construct();
		// $this->client = $client;
		$this->sales_id = $sales_id;
		$this->company_name = $company_name;
		$this->articles_cost = $articles_cost;
		$this->articles_subtotal_cost = $articles_subtotal_cost;
		$this->articles_total_price = $articles_total_price;
		$this->articles_total_cost = $articles_total_cost;
        if ($borders) {
            $this->borders = 1;
        } else {
            $this->borders = 'B';
        }

		/*
			* Se fijan los valores dependiendo los campos que se quieran mostrar
			para despues sumarlos a todos y calcular los margenes
		*/
		$widths = [];
		$widths['bar_code'] = 45;
		$widths['name'] = 55;
		$widths['price'] = 25;
		$widths['amount'] = 25;
		$widths['sub_total'] = 30;
		// 180

		if($this->articles_cost) {
			$widths['cost'] = 20;
			$widths['price'] = 20;
			$widths['amount'] = 20;
		}
		if ($this->articles_subtotal_cost) {
			$widths['sub_total_cost'] = 20;
			$widths['sub_total'] = 20;
		}

		$this->widths = $widths;

		// Se setean los magenes
		$this->margins = (210 - array_sum($widths)) / 2;
	}

	function printSales() {
		$user = Auth()->user();
		$a = 0;
		// dd(Sale::find($this->sales_id[1])->articles);
		foreach ($this->sales_id as $sale_id) {
			$a++;
            $sale = Sale::find($sale_id);
        	$client = $sale->client;            
        	$this->AddPage();
            $this->__Header($sale, $client);

            $articles = $sale->articles;

            $articulos_en_esta_pagina = 0;
            $articulos_en_esta_venta = 0;
            $suma_costos_pagina = 0;
            $suma_precios_pagina = 0;
            $suma_costos_venta = 0;
            $suma_precios_venta = 0;
            $articulos_por_pagina = 30;

			$num_page = 0;
			$cantidad_articulos = count($articles);
			// dd($articles[0]);
			$count = 0;
			$total_pages = 1;
			for ($i=0; $i < $cantidad_articulos; $i++) { 
				$count++;
				if ($count > $articulos_por_pagina) {
					$total_pages++;
					$count = 0;
				}
			}

            foreach ($articles as $article) {
                $this->SetArticleConf();	
                if ($articulos_en_esta_pagina < $articulos_por_pagina 
                	&& $articulos_en_esta_venta < $cantidad_articulos) {
                    $suma_costos_pagina += $article->cost * $article->pivot->amount;
                    $suma_precios_pagina += $article->price * $article->pivot->amount;
                    $articulos_en_esta_pagina++;
                    $articulos_en_esta_venta++;
                    $this->printArticle($article);
                } else {
	                $num_page++;
					$this->SetLineWidth(.4);
                    $this->SetY(250);
                    $this->SetX(5);
                    $this->SetFont('Arial', '', 12);
                    $this->Cell(100,7,'Página '.$num_page.' de '.$total_pages,0,0,'L');
                    if ($articulos_en_esta_pagina == 1) {
                    	$oracion = ' arículo en esta página';
                    } else {
                    	$oracion = ' arículos en esta página';
                    }
                    $this->Cell(100,7,$articulos_en_esta_pagina.$oracion,0,0,'L');
                    $this->Ln();
                    $this->SetX(5);
                    if ($this->articles_total_cost) {
                        $this->Cell(100,7,'Costos de esta página: $'.$this->price($suma_costos_pagina),0,0,'L');
                    }
                    if ($this->articles_total_price) {
                        $this->Cell(100,7,'Precios de esta página: $'.$this->price($suma_precios_pagina),0,0,'L');
                    }

                    if ($articulos_en_esta_venta < $cantidad_articulos) {
	                    $suma_costos_venta += $suma_costos_pagina;
	                    $suma_precios_venta += $suma_precios_pagina;
	                    $suma_costos_pagina = 0;
	                    $suma_precios_pagina = 0;
	                    $articulos_en_esta_pagina = 0;
	                    $suma_costos_pagina += $article->cost * $article->pivot->amount;
	                    $suma_precios_pagina += $article->price * $article->pivot->amount;
	                    $articulos_en_esta_pagina++;
	                    $articulos_en_esta_venta++;
                    	$this->AddPage();
                    	$this->__Header($sale, $client);
                    	$this->SetArticleConf();
	                    $this->printArticle($article);
                    }
                }	# Termina if 
            }	# Termina for

            $num_page++;
			$this->SetLineWidth(.4);
            $this->SetY(250);
            $this->SetX(5);
            $this->SetFont('Arial', '', 12);
            // Se terminaron los articulos de la venta
            $suma_costos_venta += $suma_costos_pagina;
            $suma_precios_venta += $suma_precios_pagina;
            $this->Cell(100,7,'Página '.$num_page.' de '.$total_pages,0,0,'L');
            if ($articulos_en_esta_pagina == 1) {
            	$oracion = ' arículo en esta página';
            } else {
            	$oracion = ' arículos en esta página';
            }
            $this->Cell(100,7,$articulos_en_esta_pagina.$oracion,0,0,'L');
            $this->Ln();
            $this->SetX(5);
            if ($this->articles_total_cost) {
                $this->Cell(100,7,'Costos de esta página: $'.$this->price($suma_costos_pagina),0,0,'L');
            	$this->Cell(100,7,'Costos de esta venta: $'.$this->price($suma_costos_venta),0,0,'L');
            	$this->Ln();
            }
            $this->SetX(5);

            
            if ($this->articles_total_price) {
                $this->Cell(100,7,'Precios de esta página: $'.$this->price($suma_precios_pagina),0,0,'L');
            	$this->Cell(100,7,'Precios de esta venta: $'.$this->price($suma_precios_venta),0,0,'L');
            }
        }
        // dd($a);
        $this->Output();
        exit;
	}

	function setArticleConf() {
        $this->setFont('Arial', '', 12);
        $this->SetDrawColor(51,51,51);
		$this->SetLineWidth(.4);
        $this->SetX($this->margins);
	}
	
	function printArticle($article) {
		$this->SetLineWidth(.4);
		$this->SetDrawColor(51,51,51);
        $this->Cell($this->widths['bar_code'],6,$article->bar_code,$this->borders,0,'L');
        $name = $article->name;
        if (strlen($name) > 20) {
            $name = substr($article->name, 0, 20) . ' ..';
        }
        $this->Cell($this->widths['name'],6,$name,$this->borders,0,'L');
        if ($this->articles_cost) {
        	$this->Cell($this->widths['cost'],6,'$'.$this->price($article->cost),$this->borders,0,'L');
        }
        $this->Cell($this->widths['price'],6,'$'.$this->price($article->price),$this->borders,0,'L');
        $this->Cell($this->widths['amount'],6,PdfArticleHelper::amount($article),$this->borders,0,'L');
        if ($this->articles_subtotal_cost) {
            $this->Cell($this->widths['sub_total_cost'],6,'$'.$this->price(PdfArticleHelper::getSubTotalCost($article)),$this->borders,0,'L');
        }
        $this->Cell($this->widths['sub_total'],6,'$'.$this->price(PdfArticleHelper::getSubTotalPrice($article)),$this->borders,0,'L');
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

	function __Header($sale, $client) {

		$user = Auth()->user();
		$this->SetXY(10, 10);

		// Si el nombre del negocio es verdaderop se escribe
		// y se pone el cursor 1cm mas abajo
		if ($this->company_name) {
			$this->SetFont('Arial', 'B', 18, 'C');
			$this->Cell(100,5,Auth()->user()->company_name,0,0,'L');
			$this->SetY(35);
		}

		// Se escribe la fecha
		$this->SetXY(100, 10);
		$this->SetFont('Arial', '', 11, 'C');
		$this->Cell(50,5,'Venta n°: '.$sale->num_sale,0,0,'R');
		if ($user->hasRole('provider')) {
			$this->Cell(50,5,'Cliente: '.$client->name,0,0,'R');
		}
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Fecha: '.date_format($sale->created_at, 'd/m/y'),0,0,'R');
		$this->Cell(50,5,'Hora: '.date_format($sale->created_at, 'H:m'),0,0,'R');
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Artículos vendidos: '.count($sale->articles),0,0,'R');
		$this->SetLineWidth(.6);
		$this->Line(5,27,205,27);

		// Se baja 1cm abajo
		$this->SetY(32);

		// Se empieza a escribir la cabecera
		$this->SetX($this->margins);
		$this->SetFont('Arial', 'B', 14, 'L');
		$this->Cell($this->widths['bar_code'], 5, 'Codigo', 0, 0, 'L');
		$this->Cell($this->widths['name'], 5, 'Artículo', 0, 0, 'L');
		if ($this->articles_cost) {
			$this->Cell($this->widths['cost'], 5, 'Costo', 0, 0, 'L');
		}
		$this->Cell($this->widths['price'], 5, 'Precio', 0, 0, 'L');
		$this->Cell($this->widths['amount'], 5, 'Cant', 0, 0, 'L');
		if ($this->articles_subtotal_cost) {
			$this->Cell($this->widths['sub_total_cost'], 5, 'Sub C', 0, 0, 'L');
		}
		$this->Cell($this->widths['sub_total'], 5, 'Sub P', 0, 0, 'L');

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
		// $this->Write(5,'Hoja '.$this->PageNo().'/{nb}');
	}
}