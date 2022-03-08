<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Client;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintArticles;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Impression;
use App\Sale;
use fpdf;
require(__DIR__.'/../fpdf/fpdf.php');

class PdfPrintSale extends fpdf {

	function __construct($sales_id, $for_commerce) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->sales = [];
		$this->for_commerce = $for_commerce;
		$this->initSales($sales_id);
		$this->borders = 'B';

		/*
			* Se fijan los valores dependiendo los campos que se quieran mostrar
			para despues sumarlos a todos y calcular los margenes
		*/
		$widths = [];
		$widths['bar_code'] = 45;
		$widths['name'] = 60;
		$widths['price'] = 35;
		$widths['amount'] = 25;
		$widths['sub_total'] = 35;
		// 180

		if($this->for_commerce) {
			$widths['name'] = 55;
			$widths['cost'] = 20;
			$widths['price'] = 20;
			$widths['amount'] = 20;
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
		$this->Y = 220;
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
            			->with('special_price')
            			->first();
            $this->sales[] = $sale;
	        $this->for_commerce ? $type = 'commerce' : $type = 'client';
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
                	$this->addArticle($article);
                } else {
	                $this->num_page++;
					$this->printPieDePagina();
        			// $this->Y = 220;
                    if ($this->articulos_en_esta_venta < $this->cantidad_articulos_de_esta_venta) {
	                    $this->resetPage();
                    	$this->AddPage();
                    	$this->__Header();
                		$this->addArticle($article);
                    }
                }	# Termina if 
            }	# Termina for

            $this->num_page++;
			$this->printPieDePagina();
            $this->reset();
        }
        // dd($a);
        $this->Output();
        exit;
	}

	function addArticle($article) {
		$this->sumarCostosYPrecios($article);
		$this->sumarCantidadDeArticulos();
        $this->printArticle($article);
	}

	function printPieDePagina() {
        $this->Y = 220;
		$this->printBorder();
        $this->printInfoPagina();
        $this->printPreciosCostosPagina();
        $this->printPreciosCostosVenta();
        $this->printPreciosConDescuento();
        $this->printSaleTypeSaleDiscounts();
        $this->printSpecialPrice();
        $this->printComisiones();
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

	function resetPage() {
		$this->suma_costos_pagina = 0;
        $this->suma_precios_pagina = 0;
        $this->articulos_en_esta_pagina = 0;
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

	function printBorder() {
		$this->SetLineWidth(.6);
		$this->Y += 10;
		$this->Line(5,$this->Y,205,$this->Y);
		// $this->Y += 5;
	}

	function printInfoPagina() {
		$this->SetY($this->Y);
        $this->SetX(5);
        $this->SetFont('Arial', '', 10);
		$this->SetLineWidth(.2);
        $this->Cell(100,5,'Página '.$this->num_page.' de '.$this->total_pages,'B',0,'L');
		$this->Y += 5;
		$this->printArticulosInfo();
	}

	function printArticulosInfo() {
        if ($this->articulos_en_esta_pagina == 1) {
        	$oracion = ' arículo en esta página';
        } else {
        	$oracion = ' arículos en esta página';
        }
        $this->Cell(100,5,$this->articulos_en_esta_pagina.$oracion,'B',0,'L');
	}

	function printPreciosCostosPagina() {
		if ($this->total_pages > 1) {
			$this->SetY($this->Y);
			$this->Y += 5;
	        $this->SetX(5);
	        $this->Cell(100,5,'Precios de esta página: $'.Numbers::price($this->suma_precios_pagina),'B',0,'L');
	        if ($this->for_commerce) {
		        // Ver la funcion price para hacerla global
	            $this->Cell(100,5,'Costos de esta página: $'.Numbers::price($this->suma_costos_pagina),'B',0,'L');
	        }
		}
	}

	function printPreciosCostosVenta() {
		$this->SetY($this->Y);
		$this->Y += 5;
        $this->SetX(5);
    	$this->Cell(100,5,'Precios de esta venta: $'.Numbers::price(SaleHelper::getTotalSale($this->sale, false)),'B',0,'L');
        if ($this->for_commerce) {
        	$this->Cell(100,5,'Costos de esta venta: $'.Numbers::price(SaleHelper::getTotalCostSale($this->sale)),'B',0,'L');
        } 
	}

	function printPreciosConDescuento() {
        if ($this->saleHasDiscounts()) {
			$this->SetY($this->Y);
	        $this->SetX(5);
	    	$this->Cell(100,5,'Precios venta con descuento: $'.Numbers::price(SaleHelper::getTotalSale($this->sale)),'B',0,'L');

			if ($this->total_pages > 1) {
				$precio_con_descuento = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina);
		 		$this->SetY($this->Y);
				// $this->Y += 5;
		    	$this->SetX(105);
		    	$this->Cell(100,5,'Precio página con descuento: $'.Numbers::price($precio_con_descuento),'B',0,'L');
			}
			$this->Y += 5;
        }
	}

	function printSpecialPrice() {
		if ($this->hasSaleSpecialPrice()) {
			// $discount = DiscountHelper::getTotalDiscountsPercentage($this->sale->discounts, true);
			$precio_con_descuento = SaleHelper::getTotalMenosDescuentos($this->sale, $this->suma_precios_pagina);
	 		$this->SetY($this->Y);
			$this->Y += 5;
	    	$this->SetX(5);
	    	$this->Cell(100,5,'Precio para '.$this->sale->special_price->name,'B',0,'L');
		}
	}

	function printSaleTypeSaleDiscounts() {
	 	$this->SetY($this->Y);
    	$this->SetX(5);
    	if ($this->hasUserSaleTypes()) {
	    	$this->Cell(100,5,'Tipo venta: '.$this->sale->sale_type->name,'B',0,'L');
			$this->Y += 5;
    	}
    	$this->printSaleDiscounts();
	}

	function printSaleDiscounts() {
    	if ($this->saleHasDiscounts()) {
	 		$this->SetY($this->Y);
			$this->Y += 5;
			$y = 5;
	    	$this->SetX(105);
	    	$this->Cell(100,5,'Descuentos: ','L',0,'L');
	    	foreach ($this->sale->discounts as $discount) {
	 			$this->SetY($this->Y);
		    	$this->SetX(105);
	    		$this->Cell(100,5,$discount->name . ' ' . $discount->percentage . '%','LB',0,'L');
	    		$y += 5;
	    		$this->Y += 5;
	    	}
	    	$this->Y -= $y;
    	}
	}

	function hasUserSaleTypes() {
		return count(Auth()->user()->sale_types) >= 1;
	}

	function saleHasDiscounts() {
		if (count($this->sale->discounts) >= 1) {
			return true;
		}
		return false;
	}

	function printComisiones() {
		if ($this->for_commerce && $this->hasSaleCommissions()) {
			if ($this->saleHasDiscounts()) {
				$this->Y += 5;
			} 
	 		$this->SetY($this->Y);
	    	$this->SetX(5);
	    	$this->Cell(100,5,'Comisiones: ','B',0,'L');
	    	// $commissioners = $this->getCommission();
	    	// dd($commissioners);
	    	$commissions = $this->getCommissions();
	    	foreach ($commissions as $commission) {
				$this->Y += 5;
	 			$this->SetY($this->Y);
		    	$this->SetX(5);
		    	$this->Cell(25,5,$commission->commissioner->name . ' ' . $commission->percentage . '%' , 1,0,'L');
		    	$this->Cell(25,5, '$'.Numbers::price($commission->monto) , 1,0,'L');
	    	}
			$this->Y += 5;
 			$this->SetY($this->Y);
	    	$this->SetX(5);
		    	$this->Cell(50,5, 'Total: ','B',0,'L');
		    $this->Cell(50,5, '$'.$this->getTotalMenosComisiones() ,'B',0,'L');
		}
	}

	function hasSaleCommissions() {
		return count($this->sale->commissions) >= 1;
	}

	function hasSaleSpecialPrice() {
		return !is_null($this->sale->special_price_id);
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
    	$this->SetArticleConf();
        $this->Cell($this->widths['bar_code'],6,$article->bar_code,$this->borders,0,'L');
        $name = ArticleHelper::getShortName($article->name, 20);
        $this->Cell($this->widths['name'],6,$name,$this->borders,0,'L');
        if ($this->for_commerce) {
        	$this->Cell($this->widths['cost'],6,'$'.Numbers::price($article->pivot->cost),$this->borders,0,'L');
        }
        $this->Cell($this->widths['price'],6,'$'.Numbers::price($article->pivot->price),$this->borders,0,'L');
        $this->Cell($this->widths['amount'],6,PdfArticleHelper::amount($article),$this->borders,0,'L');
        if ($this->for_commerce) {
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
		$this->SetFont('Arial', 'B', 18, 'C');
		$this->Cell(100,5,Auth()->user()->company_name,0,0,'L');
		$this->SetY(35);

		// Se escribe la fecha
		$this->SetXY(100, 5);
		$this->SetFont('Arial', '', 11, 'C');
		$this->Cell(50,5,'Venta n°: '.$this->sale->num_sale,0,0,'L');
		if ($this->sale->client_id) {
			$this->Cell(50,5,'Cliente: '.$this->client->name,0,0,'L');
		}
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Fecha: '.date_format($this->sale->created_at, 'd/m/y'),0,0,'L');
		$this->Cell(50,5,'Hora: '.date_format($this->sale->created_at, 'H:m'),0,0,'L');
		$this->Ln();
		$this->SetX(100);
		$this->Cell(50,5,'Artículos vendidos: '.count($this->sale->articles),0,0,'L');
		$this->SetLineWidth(.6);
		$this->Line(5,25,205,25);

		// Se baja 1cm abajo
		$this->SetY(27);

		// Se empieza a escribir la cabecera
		$this->SetX($this->margins);
		$this->SetFont('Arial', 'B', 14, 'L');
		$this->Cell($this->widths['bar_code'], 5, 'Codigo', 0, 0, 'L');
		$this->Cell($this->widths['name'], 5, 'Artículo', 0, 0, 'L');
		if ($this->for_commerce) {
			$this->Cell($this->widths['cost'], 5, 'Costo', 0, 0, 'L');
		}
		$this->Cell($this->widths['price'], 5, 'Precio', 0, 0, 'L');
		$this->Cell($this->widths['amount'], 5, 'Cant', 0, 0, 'L');
		if ($this->for_commerce) {
			$this->Cell($this->widths['sub_total_cost'], 5, 'Sub C', 0, 0, 'L');
		}
		$this->Cell($this->widths['sub_total'], 5, 'Sub P', 0, 0, 'L');

		// Se dibuja la linea celeste que separa el thead del tbody
		$this->SetLineWidth(.6);
		// $this->SetDrawColor(100, 174, 238);
		$this->Line($this->margins, 34, $this->margins+array_sum($this->widths), 34);

		$this->SetY(34);
	}

	function __Footer() {
		$this->SetFont('Arial', '', 11);
		$this->AliasNbPages();
		$this->SetY(-30);
		// $this->Write(5,'Hoja '.$this->num_PageNo().'/{nb}');
	}
}