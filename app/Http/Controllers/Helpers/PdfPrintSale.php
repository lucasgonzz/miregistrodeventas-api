<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Client;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintArticles;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Impression;
use App\Sale;
use fpdf;
require(__DIR__.'/../fpdf/fpdf.php');

class PdfPrintSale extends fpdf {

	function __construct($sales_id, $company_name, $articles_cost, $articles_subtotal_cost, $articles_total_price, $articles_total_cost, $borders) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		// $this->client = $client;
		$this->sales = [];
		$this->company_name = $company_name;
		$this->articles_cost = $articles_cost;
		$this->articles_subtotal_cost = $articles_subtotal_cost;
		$this->articles_total_price = $articles_total_price;
		$this->articles_total_cost = $articles_total_cost;
		$this->initSales($sales_id);
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

        $this->articulos_por_pagina = 30;

        $this->articulos_en_esta_pagina = 0;
        $this->suma_costos_pagina = 0;
        $this->suma_precios_pagina = 0;
        
        $this->articulos_en_esta_venta = 0;
        // $this->suma_costos_venta = 0;
        // $this->suma_precios_venta = 0;

        $this->cantidad_articulos_de_esta_venta = 0;

		$this->num_page = 0;
	}

	function initSales($sales_id) {
		foreach ($sales_id as $sale_id) {
            $sale = Sale::where('id', $sale_id)
            			->with('client')
            			->with('articles')
            			->with('discounts')
            			// ->with('commissioners.seller')
            			->with('commissions')
            			->with('sale_type')
            			->first();
            $this->sales[] = $sale;
	        $this->articles_total_cost ? $type = 'commerce' : $type = 'client';
        	Impression::create([
        		'sale_id' => $sale_id,
        		'type'    => $type,
        	]);
		}
	}

	function printSales() {
		$user = Auth()->user();
		// dd(Sale::find($this->sales_id[1])->articles);
		foreach ($this->sales as $sale) {
			$this->sale = $sale;
        	$client = $sale->client;    
			$this->client = $client;
        	$this->AddPage();
            $this->__Header();

			$this->cantidad_articulos_de_esta_venta = count($sale->articles);
			$this->setTotalPaginas();

            foreach ($this->sale->articles as $article) {
                $this->SetArticleConf();	
                if ($this->articulos_en_esta_pagina < $this->articulos_por_pagina 
                	&& $this->articulos_en_esta_venta < $this->cantidad_articulos_de_esta_venta) {
                	$this->sumarCostosYPrecios($article);
                	$this->sumarCantidadDeArticulos();
                    $this->printArticle($article);
                } else {
	                $this->num_page++;
		            $this->printInfoPagina();
			        $this->printArticulosInfo();
		            $this->printPreciosPagina();
            		$this->printPreciosPaginaConDescuento();
            		$this->printComisiones();

                    if ($this->articulos_en_esta_venta < $this->cantidad_articulos_de_esta_venta) {
	                    // $this->suma_costos_venta += $this->suma_costos_pagina;
	                    // $this->suma_precios_venta += $this->suma_precios_pagina;
	                    $this->suma_costos_pagina = 0;
	                    $this->suma_precios_pagina = 0;
	                    $this->articulos_en_esta_pagina = 0;
	                    $this->suma_costos_pagina += $article->pivot->cost * $article->pivot->amount;
	                    $this->suma_precios_pagina += $article->pivot->price * $article->pivot->amount;
	                    // $this->suma_precios_venta += $article->pivot->price * $article->pivot->amount;
                		$this->sumarCantidadDeArticulos();
                    	$this->AddPage();
                    	$this->__Header();
                    	$this->SetArticleConf();
	                    $this->printArticle($article);
                    }
                }	# Termina if 
            }	# Termina for

            $this->num_page++;
			// $this->SetLineWidth(.4);
	        // $this->suma_costos_venta += $this->suma_costos_pagina;
	        // $this->suma_precios_venta += $this->suma_precios_pagina;
            $this->printInfoPagina();
	        $this->printArticulosInfo();
            $this->printPreciosPagina();
            $this->printPreciosVenta();
            $this->printPreciosPaginaConDescuento();
            $this->printComisiones();
            $this->reset();
        }
        // dd($a);
        $this->Output();
        exit;
	}

	function reset() {
		$this->num_page = 0;
		$this->articulos_en_esta_pagina = 0;
		$this->articulos_en_esta_venta = 0;

        // SaleHelper::getTotalCostSale($this->sale) = 0;
        // $this->suma_precios_venta = 0;
        $this->suma_costos_pagina = 0;
        $this->suma_precios_pagina = 0;
	}

	function setTotalPaginas() {
		$count = 0;
		$this->total_pages = 1;
		for ($i=0; $i < $this->cantidad_articulos_de_esta_venta; $i++) { 
			$count++;
			if ($count > $this->articulos_por_pagina) {
				$this->total_pages++;
				$count = 0;
			}
		}
	}

	function printInfoPagina() {
		$this->SetY(225);
        $this->SetX(5);
        $this->SetFont('Arial', '', 10);
        $this->Cell(100,5,'Página '.$this->num_page.' de '.$this->total_pages,0,0,'L');
	}

	function printArticulosInfo() {
        if ($this->articulos_en_esta_pagina == 1) {
        	$oracion = ' arículo en esta página';
        } else {
        	$oracion = ' arículos en esta página';
        }
        $this->Cell(100,5,$this->articulos_en_esta_pagina.$oracion,0,0,'L');
	}

	function printPreciosPagina() {
		$this->SetY(230);
        $this->SetX(5);
        $this->Cell(100,5,'Precios de esta página: $'.Numbers::price($this->suma_precios_pagina),0,0,'L');
        if ($this->articles_total_cost) {
	        // Ver la funcion price para hacerla global
            $this->Cell(100,5,'Costos de esta página: $'.Numbers::price($this->suma_costos_pagina),0,0,'L');
        }
	}

	function printPreciosVenta() {
		$this->SetY(235);
        $this->SetX(5);
    	$this->Cell(100,5,'Precios de esta venta: $'.Numbers::price(SaleHelper::getTotalSale($this->sale, false)),0,0,'L');
        if ($this->articles_total_cost) {
        	$this->Cell(100,5,'Costos de esta venta: $'.Numbers::price(SaleHelper::getTotalCostSale($this->sale)),0,0,'L');
        } 
        if ($this->saleHasDiscounts()) {
			$this->SetY(240);
	        $this->SetX(5);
	    	$this->Cell(100,5,'Precios de esta venta con descuento: $'.Numbers::price(SaleHelper::getTotalSale($this->sale)),0,0,'L');
        }
	}

	function printPreciosPaginaConDescuento() {
		if ($this->saleHasDiscounts()) {
			// $discount = DiscountHelper::getTotalDiscountsPercentage($this->sale->discounts, true);
			$precio_con_descuento = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina);
	    	$Y = 245;
	 		$this->SetY($Y);
	    	$this->SetX(5);
	    	$this->Cell(100,5,'Precio página con descuento: $'.Numbers::price($precio_con_descuento),0,0,'L');
		}
		$Y = 245;
 		$this->SetY($Y);
    	$this->SetX(105);
    	$this->Cell(100,5,'Tipo venta: '.$this->sale->sale_type->name,0,0,'L');
    	if ($this->saleHasDiscounts()) {
	    	$Y += 5;
	 		$this->SetY($Y);
	    	$this->SetX(105);
	    	$this->Cell(100,5,'Descuentos: ',0,0,'L');
	    	foreach ($this->sale->discounts as $discount) {
		 		$Y += 5;
		 		$this->SetY($Y);
		    	$this->SetX(105);
	    		$this->Cell(100,5,$discount->name . ' ' . $discount->percentage . '%',0,0,'L');
	    	}
    	}
    	// $this->Cell(100,5,'Precios con descuento: '.Numbers::percentage($discount),0,0,'L');
	}

	function saleHasDiscounts() {
		if (count($this->sale->discounts) >= 1) {
			return true;
		}
		return false;
	}

	function printComisiones() {
		if ($this->articles_total_cost) {
			if ($this->saleHasDiscounts()) {
				$Y = 250;
			} else {
				$Y = 245;
			}
	 		$this->SetY($Y);
	    	$this->SetX(5);
	    	$this->Cell(100,5,'Comisiones: ',0,0,'L');
	    	// $commissioners = $this->getCommission();
	    	// dd($commissioners);
	    	$commissions = $this->getCommissions();
	    	foreach ($commissions as $commission) {
	    		$Y += 5;
		 		$this->SetY($Y);
		    	$this->SetX(5);
		    	$this->Cell(50,5,$commission->commissioner->name . ' ' . $commission->percentage . '%' ,0,0,'L');
		    	$this->Cell(50,5, '$'.Numbers::price($commission->monto) ,0,0,'L');
	    	}
			$Y += 5;
	 		$this->SetY($Y);
	    	$this->SetX(5);
		    	$this->Cell(50,5, 'Total: ',0,0,'L');
		    $this->Cell(50,5, '$'.$this->getTotalMenosComisiones() ,0,0,'L');
		}
	}

	function getCommissions() {
		$commissions = [];
		foreach ($this->sale->commissions as $commission) {
			if ($commission->page == $this->num_page) {
				$commissions[] = $commission;
			}
		}
		return $commissions;
	}

	function getTotalMenosComisiones() {
		$total = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina);
		$commissions = $this->getCommissions();
		foreach ($commissions as $commission) {
			$total -= $commission->monto;
		}
		return Numbers::price($total);
	}

	function getCommission() {
		$commissioners = [];
		$discount = DiscountHelper::getTotalDiscountsPercentage($this->sale->discounts, true);
		$total = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina);
		$seller = $this->getSellerFromCommissioners();
		if (!is_null($seller)) {
			if ($discount < 10) {
				$commission_seller = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina) * Numbers::percentage($seller->pivot->percentage);
				$total -= $commission_seller;
				$commissioners[$seller->name] = Numbers::price($commission_seller);
				// $new_total = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina) - $commission_seller;
				foreach ($this->sale->commissioners as $commissioner) {
					if ($commissioner->pivot->is_seller == 0) {
						if (!is_null($seller->seller->seller_id)) {
							$total_a_restar = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina) - $commission_seller;
						} else {
							$total_a_restar = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina);
						}
						$comision = $total_a_restar * Numbers::percentage($commissioner->pivot->percentage);
						$total -= $comision;
						$commissioners[$commissioner->name] = Numbers::price($comision);
					}
				}
			} else if ($discount >= 10) {
				$commission_seller = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina) * Numbers::percentage($seller->pivot->percentage);
				$total -= $commission_seller;
				$commissioners[$seller->name] = Numbers::price($commission_seller);
				foreach ($this->sale->commissioners as $commissioner) {
					if ($commissioner->pivot->is_seller == 0) {
						$comision = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina) * Numbers::percentage($commissioner->pivot->percentage);
						$total -= $comision;
						$commissioners[$commissioner->name] = Numbers::price($comision);
					}
				}
			}
		} else {
			foreach ($this->sale->commissioners as $commissioner) {
				if ($commissioner->pivot->is_seller == 0) {
					$total_a_restar = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina);
					$comision = $total_a_restar * Numbers::percentage($commissioner->pivot->percentage);
					$total -= $comision;
					$commissioners[$commissioner->name] = Numbers::price($comision);
				}
			}
		}
		$commissioners['total'] = Numbers::price($total);
		return $commissioners;
	}

	function getSellerFromCommissioners() {
		foreach ($this->sale->commissioners as $commissioner) {
			if ($commissioner->pivot->is_seller == 1) {
				return $commissioner;
			}
		}
		return null;
	}

	function sumarCostosYPrecios($article) {
        $this->suma_costos_pagina += $article->pivot->cost * $article->pivot->amount;
        $this->suma_precios_pagina += $article->pivot->price * $article->pivot->amount;
	}

	function sumarCantidadDeArticulos() {
        $this->articulos_en_esta_pagina++;
        $this->articulos_en_esta_venta++;
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
        	$this->Cell($this->widths['cost'],6,'$'.Numbers::price($article->pivot->cost),$this->borders,0,'L');
        }
        $this->Cell($this->widths['price'],6,'$'.Numbers::price($article->pivot->price),$this->borders,0,'L');
        $this->Cell($this->widths['amount'],6,PdfArticleHelper::amount($article),$this->borders,0,'L');
        if ($this->articles_subtotal_cost) {
            $this->Cell($this->widths['sub_total_cost'],6,'$'.Numbers::price(PdfArticleHelper::getSubTotalCost($article)),$this->borders,0,'L');
        }
        $this->Cell($this->widths['sub_total'],6,'$'.Numbers::price(PdfArticleHelper::getSubTotalPrice($article)),$this->borders,0,'L');
        $this->Ln();
    }

	function __Header() {

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
		$this->Cell(50,5,'Venta n°: '.$this->sale->num_sale,0,0,'R');
		if ($user->hasRole('provider')) {
			$this->Cell(50,5,'Cliente: '.$this->client->name,0,0,'R');
		}
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Fecha: '.date_format($this->sale->created_at, 'd/m/y'),0,0,'R');
		$this->Cell(50,5,'Hora: '.date_format($this->sale->created_at, 'H:m'),0,0,'R');
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Artículos vendidos: '.count($this->sale->articles),0,0,'R');
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
		// $this->Write(5,'Hoja '.$this->num_PageNo().'/{nb}');
	}
}