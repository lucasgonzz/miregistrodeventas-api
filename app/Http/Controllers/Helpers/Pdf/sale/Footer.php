<?php

namespace App\Http\Controllers\Helpers\pdf\sale;

use App\Article;
use App\Client;
use App\Http\Controllers\Helpers\AfipHelper;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfPrintArticles;
use App\Http\Controllers\Helpers\pdf\sale\Index;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Impression;
use App\Sale;
// use fpdf;
// require(__DIR__.'/../../../fpdf/fpdf.php');

class Footer extends Index {

	function __construct($afip_ticket) {
		// parent::__construct();
		// $this->SetAutoPageBreak(true, 1);
		$this->afip_ticket = $afip_ticket;
	}

	function print() {
		if ($this->afip_ticket) {
	        $this->printOtrosTibutos();
	        $this->printImportes();
		} else {
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
	}

	function printOtrosTibutos() {
		$this->setY(180);
		$this->setX(6);
		$this->Cell(60, 5, 'Otros Tributos', 1, 0, 'L');
		$this->setX(6);
		// Header
		$this->y += 5;
		$this->Cell(60, 5, 'Descripción', 1, 0, 'L');
		$this->Cell(30, 5, 'Detalle', 1, 0, 'L');
		$this->Cell(15, 5, 'Alíc. %', 1, 0, 'C');
		$this->Cell(20, 5, 'Importe', 1, 0, 'R');
		// Body
		// 1
		$this->y += 5;
		$this->setX(6);
		$this->Cell(60, 5, 'Per./Ret. de Impuesto a las Ganancias', 1, 0, 'L');
		$this->setX(111);
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 2
		$this->y += 5;
		$this->setX(6);
		$this->Cell(60, 5, 'Per./Ret. de Iva', 1, 0, 'L');
		$this->setX(111);
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 3
		$this->y += 5;
		$this->setX(6);
		$this->Cell(60, 5, 'Per./Ret. Ingresos Brutos', 1, 0, 'L');
		$this->setX(111);
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 4
		$this->y += 5;
		$this->setX(6);
		$this->Cell(60, 5, 'Impuestos Internos', 1, 0, 'L');
		$this->setX(111);
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 5
		$this->y += 5;
		$this->setX(6);
		$this->Cell(60, 5, 'Impuestos Municipales', 1, 0, 'L');
		$this->setX(111);
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 6
		$this->y += 5;
		$this->setX(6);
		$this->Cell(60, 5, 'Importe Otros Tributos', 1, 0, 'L');
		$this->setX(111);
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
	}

	function printImportes() {
		$importes = AfipHelper::getImportes($this->sale);
		$this->setY(190);
		$this->setX(140);
		$this->SetFont('Arial', 'B', 9);
		// 1
		$this->Cell(40, 5, 'Importe Neto Gravado: $', 1, 0, 'R');
		$this->Cell(20, 5, Numbers::price($importes['importe_gravado']), 1, 0, 'R');
		// 2
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'IVA 27%: $', 1, 0, 'R');
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 3
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'IVA 21%: $', 1, 0, 'R');
		$this->Cell(20, 5, Numbers::price($importes['importe_iva']), 1, 0, 'R');
		// 4
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'IVA 10.5%: $', 1, 0, 'R');
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 5
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'IVA 5%: $', 1, 0, 'R');
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 6
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'IVA 2.5%: $', 1, 0, 'R');
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 7
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'IVA 0%: $', 1, 0, 'R');
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 8
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'Importe Otros Tributos: $', 1, 0, 'R');
		$this->Cell(20, 5, '0,00', 1, 0, 'R');
		// 9
		$this->y += 5;
		$this->setX(140);
		$this->Cell(40, 5, 'Importe Total: $', 1, 0, 'R');
		$this->Cell(20, 5, Numbers::price($importes['importe_total']), 1, 0, 'R');
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

}