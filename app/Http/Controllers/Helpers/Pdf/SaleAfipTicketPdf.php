<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Article;
use App\Client;
use App\Http\Controllers\Helpers\AfipHelper;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\PdfArticleHelper;
use App\Http\Controllers\Helpers\PdfPrintArticles;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\StringHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Impression;
use App\Sale;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class SaleAfipTicketPdf extends fpdf {

	function __construct($sale_id) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 1);
		$this->sale = SaleHelper::getFullModel($sale_id);
		$this->client = $this->sale->client;
		$this->borders = 'B';
        $this->printing_duplicate = false;
        $this->user = UserHelper::getFullModel();

		$widths = [];
		$widths['codigo'] = 23;
		$widths['producto'] = 50;
		$widths['cantidad'] = 17;
		$widths['unidad_medida'] = 16;
		$widths['bonif'] = 15;
		$widths['subtotal'] = 20;
		$widths['iva'] = 14;
		$widths['subtotal_con_iva'] = 25;
		$widths['subtotal_final'] = 45;

		if ($this->sale->afip_ticket->cbte_tipo == 1) {
			$widths['precio_unitario'] = 20;
			$widths['subtotal'] = 20;
		} else {
			$widths['precio_unitario'] = 35;
			$widths['subtotal'] = 40;
		}

		$this->widths = $widths;

		// Se setean los magenes
		$this->margins = (210 - array_sum($widths)) / 2;

        $this->articulos_por_pagina = 14;

        $this->articulos_en_esta_pagina = 0;
        $this->suma_costos_pagina = 0;
        $this->suma_precios_pagina = 0;
        
        $this->articulos_en_esta_venta = 0;

        $this->cantidad_articulos_de_esta_venta = 0;

		$this->num_page = 0;
		$this->Y = 220;
	}

	function saveImpression($sale) {
		$this->for_commerce ? $type = 'commerce' : $type = 'client';
        $impression = Impression::where('sale_id', $sale->id)
        						->where('type', $type)
        						->first();
        if (is_null($impression)) {
        	Impression::create([
        		'sale_id' => $sale->id,
        		'type'    => $type,
        	]);
        }
	}

	function printSales() {
		$user = Auth()->user();
    	$this->AddPage();
        $this->__Header();

		$this->cantidad_articulos_de_esta_venta = count($this->sale->articles);
		$this->setTotalPaginas();

        foreach ($this->sale->articles as $article) {
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
        if (!$this->printing_duplicate) {
        	$this->printing_duplicate = true;
        	$this->printSales();
        } else {
	        $this->Output();
	        exit;
        }
	}

	function addArticle($article) {
		$this->sumarCostosYPrecios($article);
		$this->sumarCantidadDeArticulos();
        $this->printArticle($article);
	}

	function printPieDePagina() {
        $this->printOtrosTibutos();
        $this->printImportes();
        $this->printLine();
        $this->printPhone();
        $this->printAfipData();
        $this->printQR();
	}

	function printOtrosTibutos() {
		if ($this->sale->afip_ticket->cbte_tipo == 1) {
			$this->setY(180);
			$this->setX(6);
			$this->SetFont('Arial', '', 9);
			$this->Cell(60, 5, 'Otros Tributos', 0, 0, 'L');
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
			$this->Cell(60, 5, 'Per./Ret. de Impuesto a las Ganancias', 0, 0, 'L');
			$this->setX(111);
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
			// 2
			$this->y += 5;
			$this->setX(6);
			$this->Cell(60, 5, 'Per./Ret. de Iva', 0, 0, 'L');
			$this->setX(111);
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
			// 3
			$this->y += 5;
			$this->setX(6);
			$this->Cell(60, 5, 'Per./Ret. Ingresos Brutos', 0, 0, 'L');
			$this->setX(111);
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
			// 4
			$this->y += 5;
			$this->setX(6);
			$this->Cell(60, 5, 'Impuestos Internos', 0, 0, 'L');
			$this->setX(111);
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
			// 5
			$this->y += 5;
			$this->setX(6);
			$this->Cell(60, 5, 'Impuestos Municipales', 0, 0, 'L');
			$this->setX(111);
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
			// 6
			$this->y += 5;
			$this->setX(6);
			$this->Cell(60, 5, 'Importe Otros Tributos', 0, 0, 'L');
			$this->setX(111);
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
		}
	}

	function printImportes() {
		$importes = AfipHelper::getImportes($this->sale);
		$this->setX(140);
		if ($this->sale->afip_ticket->cbte_tipo == 1) {
			$this->setY(190);
			$this->setX(140);
			$this->SetFont('Arial', 'B', 9);
			// 1
			$this->Cell(40, 5, 'Importe Neto Gravado: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['gravado']), 0, 0, 'R');
			// 2
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'IVA 27%: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['ivas']['27']['Importe']), 0, 0, 'R');
			// 3
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'IVA 21%: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['ivas']['21']['Importe']), 0, 0, 'R');
			// 4
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'IVA 10.5%: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['ivas']['10']['Importe']), 0, 0, 'R');
			// 5
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'IVA 5%: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['ivas']['5']['Importe']), 0, 0, 'R');
			// 6
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'IVA 2.5%: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['ivas']['2']['Importe']), 0, 0, 'R');
			// 7
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'IVA 0%: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['ivas']['0']['Importe']), 0, 0, 'R');
			// 8
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'Importe Otros Tributos: $', 0, 0, 'R');
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
			// 9
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'Importe Total: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['total']), 0, 0, 'R');
		} else {
			// 8
			$this->setY(225);
			$this->setX(140);
			$this->Cell(40, 5, 'Importe Otros Tributos: $', 0, 0, 'R');
			$this->Cell(20, 5, '0,00', 0, 0, 'R');
			// 9
			$this->y += 5;
			$this->setX(140);
			$this->Cell(40, 5, 'Importe Total: $', 0, 0, 'R');
			$this->Cell(20, 5, Numbers::price($importes['total']), 0, 0, 'R');
		}
	}

	function printLine() {
		$this->SetLineWidth(.4);
		if ($this->sale->afip_ticket->cbte_tipo == 1) {
			// Izquierda
			$this->Line(5,180,5,235);
			// Abajo
			$this->Line(5,235,205,235);
			// Derecha
			$this->Line(205,180,205,235);
			// Arriba
			$this->Line(5,180,205,180);
		} else {
			// Izquierda
			$this->Line(5,220,5,235);
			// Abajo
			$this->Line(5,235,205,235);
			// Derecha
			$this->Line(205,220,205,235);
			// Arriba
			$this->Line(5,220,205,220);
		}
	}

	function printPhone() {
		$this->y = 237;
		$this->x = 5;
		$this->Cell(200, 10, 'Telefono: '.Auth()->user()->phone, 1, 0, 'C');
	}

	function printAfipData() {
		// Page
		$this->y += 12;
		$this->x = 55;
		$this->Cell(100, 5, 'Pág. '.$this->num_page, 0, 0, 'C');
		// Cae
		$this->y += 5;
		$this->x = 105;
		$this->SetFont('Arial', 'B', 10);
		$this->Cell(50, 5, 'CAE N°:', 0, 0, 'R');
		$this->SetFont('Arial', '', 10);
		$this->Cell(50, 5, $this->sale->afip_ticket->cae, 0, 0, 'L');
		// Cae vencimiento
		$this->y += 5;
		$this->x = 105;
		$this->SetFont('Arial', 'B', 10);
		$this->Cell(50, 5, 'Fecha de Vto. de CAE:', 0, 0, 'R');
		$this->SetFont('Arial', '', 10);
		$this->Cell(50, 5, date_format($this->sale->afip_ticket->cae_expired_at, 'd/m/Y'), 0, 0, 'L');
	}

	function printQR() {
		// $this->x = 5;
		$data = [
			'ver' 			=> 1,
			'fecha' 		=> date_format($this->sale->afip_ticket->created_at, 'Y-m-d'),
			'cuit' 			=> $this->sale->afip_ticket->cuit_negocio,
			'ptoVta' 		=> $this->sale->afip_ticket->punto_venta,
			'tipoCmp' 		=> $this->sale->afip_ticket->cbte_tipo,
			'nroCmp' 		=> $this->sale->afip_ticket->cbte_numero,
			'importe' 		=> $this->sale->afip_ticket->importe_total,
			'moneda' 		=> $this->sale->afip_ticket->moneda_id,
			'ctz' 			=> 1,
			'tipoDocRec' 	=> AfipHelper::getDocType('Cuit'),
			'nroDocRec' 	=> $this->sale->afip_ticket->cuit_cliente,
			'codAut' 		=> $this->sale->afip_ticket->cae,
		];
		$afip_link = 'https://www.afip.gob.ar/fe/qr/?'.base64_encode(json_encode($data));
		$url = "http://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=$afip_link&.png";
        $this->Image($url, 0, 250, 50);
        $this->Image(public_path().'/afip/logo.png', 45, 260, 40);
        $this->x = 45;
        $this->y = 270;
        $this->SetFont('Arial', 'BI', 10);
		$this->Cell(50, 5, 'Comprobante Autorizado', 0, 0, 'L');
        $this->SetFont('Arial', '', 7);
        $this->x = 45;
		$this->y += 5;
		$this->Cell(150, 5, 'Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación', 0, 0, 'L');
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
		// $this->Line(5,190,5,230);
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
    	// if ($this->hasUserSaleTypes()) {
    	if (!is_null($this->sale->sale_type)) {
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

	function getCost($article) {
		if (!is_null($article->pivot->cost)) {
			return $article->pivot->cost;
		}
		return $article->cost;
	}

	function sumarCostosYPrecios($article) {
        $this->suma_costos_pagina += $this->getCost($article) * $article->pivot->amount;
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
        $this->SetX(5);
	}
	
	function printArticle($article) {
	    $this->SetArticleConf();
    	$this->setFont('Arial', '', 8);
        $this->Cell($this->widths['codigo'],6,StringHelper::short($article->bar_code, 14),0,0,'L');
        $this->Cell($this->widths['producto'],6,StringHelper::short($article->name, 30),0,0,'L');
        $this->Cell($this->widths['cantidad'],6,$article->pivot->amount,0,0,'R');
        $this->Cell($this->widths['unidad_medida'],6,'unidad',0,0,'C');
        $this->Cell($this->widths['precio_unitario'],6,Numbers::price(AfipHelper::getArticlePrice($article, $this->client)),0,0,'R');

        $this->Cell($this->widths['bonif'],6,'0.00',0,0,'R');
        $this->Cell($this->widths['subtotal'],6,Numbers::price(AfipHelper::getImporteNeto($article, $this->client)),0,0,'R');
		if ($this->sale->afip_ticket->cbte_tipo == 1) {
        	$this->Cell($this->widths['iva'],6,$article->iva->percentage,0,0,'C');
        	$this->Cell($this->widths['subtotal_con_iva'],6,Numbers::price(PdfArticleHelper::getSubTotalPrice($article)),0,0,'R');
		}
        $this->y += 6;
    }

	function __Header() {
		$this->SetXY(5, 5);
		$this->printTicketCommerceInfo();
		$this->printClientInfo();
		$this->SetY(85);
		$this->printTableHeader();
	}

	function printTicketCommerceInfo() {
		$this->SetFont('Arial', 'B', 14, 'C');
		if (!$this->printing_duplicate) {
			$this->Cell(200,10,'ORIGINAL','T-B',0,'C');
		} else {
			$this->Cell(200,10,'DUPLICADO','T-B',0,'C');
		}
		$this->printCbteTipo();
		$this->printCommerceInfo();
		$this->printAfipTicketInfo();
		$this->printCommerceLines();
	}

	function printClientInfo() {
		// Cuit
		$this->SetY(62);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(10,7,'CUIT:',0,0,'L');
		$this->SetFont('Arial', '', 8);
		$this->Cell(20,7,$this->sale->afip_ticket->cuit_cliente,0,0,'C');
		// Iva
		$this->SetY(69);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(37,7,'Condición frente al IVA:',0,0,'L');
		$this->SetFont('Arial', '', 8);
		if ($this->sale->afip_ticket->iva_cliente != '') {
			$this->Cell(50,7,'IVA '.$this->sale->afip_ticket->iva_cliente,0,0,'L');
		} else {
			$this->Cell(50,7,'IVA consumidor final',0,0,'L');
		}
		// $this->Cell(50,7,'IVA '.$this->sale->afip_ticket->iva_cliente,0,0,'L');
		// Iva
		$this->SetY(76);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(32,7,'Condición de venta:',0,0,'L');
		$this->SetFont('Arial', '', 8);
		$this->Cell(50,7,'Contado',0,0,'L');
		// Razon social
		if (!is_null($this->sale->client)) {
			$this->SetY(62);
			$this->SetX(80);
			$this->SetFont('Arial', 'B', 8);
			$this->Cell(47,7,'Apellido y Nombre / Razón Social:',0,0,'L');
			$this->SetFont('Arial', '', 8);
			$this->Cell(60,7,$this->sale->client->razon_social,0,0,'L');
			$this->SetY(69);
			$this->SetX(97);
			$this->SetFont('Arial', 'B', 8);
			$this->Cell(30,7,'Domicilio Comercial:',0,0,'L');
			$this->SetFont('Arial', '', 8);
			$this->Cell(60,7,$this->sale->client->address,0,0,'L');
		}
		$this->printClientLines();
	}

	function printCbteTipo() {
		$this->SetY(15);
		$this->SetX(97);
		$this->SetFont('Arial', 'B', 20);
		$this->Cell(16,16, $this->sale->afip_ticket->cbte_letra,1,0,'C');
		$this->SetY(26);
		$this->SetX(97);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(16,5,'COD. '.$this->sale->afip_ticket->cbte_tipo,0,0,'C');
	}

	function printCommerceInfo() {
		// Razon social
		$this->SetY(30);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(25,10,'Razón Social:',0,0,'L');
		$this->SetFont('Arial', '', 9);
		$this->Cell(50,10,Auth()->user()->afip_information->razon_social,0,0,'L');
		// Domicilio
		$this->SetY(40);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(35,10,'Domicilio Comercial:',0,0,'L');
		$this->SetFont('Arial', '', 9);
		$this->Cell(50,10,Auth()->user()->afip_information->domicilio_comercial,0,0,'L');
		// Iva
		$this->SetY(50);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(40,10,'Condición frente al IVA:',0,0,'L');
		$this->SetFont('Arial', 'B', 9);
		// $this->Cell(50,10,'IVA '.Auth()->user()->iva->name,0,0,'L');
		$this->Cell(50,10,'IVA '.$this->sale->afip_ticket->iva_negocio,0,0,'L');
	}

	function printAfipTicketInfo() {
		// Titulo factura
		$this->SetY(20);
		$this->SetX(118);
		$this->SetFont('Arial', 'B', 18);
		$this->Cell(35,10,'FACTURA',0,0,'L');
		// Punto de venta y numero de cbte
		$this->SetY(30);
		$this->SetX(118);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(27,5,'Punto de Venta:',0,0,'L');
		$this->Cell(15,5,$this->getPuntoVenta(),0,0,'L');
		$this->Cell(21,5,'Comp. Nro:',0,0,'L');
		$this->Cell(27,5,$this->getNumCbte(),0,0,'L');
		// Fecha 
		$this->SetY(35);
		$this->SetX(118);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(32,5,'Fecha de Emisión:',0,0,'L');
		$this->Cell(20,5,date_format($this->sale->afip_ticket->created_at, 'd/m/Y'),0,0,'L');
		// Cuit 
		$this->SetY(45);
		$this->SetX(118);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(12,5,'CUIT:',0,0,'L');
		$this->Cell(25,5,$this->sale->afip_ticket->cuit_negocio,0,0,'L');
		// Ingresos brutos 
		$this->SetY(50);
		$this->SetX(118);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(30,5,'Ingresos Brutos:',0,0,'L');
		$this->Cell(25,5,Auth()->user()->ingresos_brutos,0,0,'L');
		
		// Inicio actividades
		if ($this->user->afip_information->inicio_actividades != '') {
			$this->SetY(55);
			$this->SetX(118);
			$this->SetFont('Arial', 'B', 9);
			$this->Cell(52,5,'Fecha de Inicio de Actividades:',0,0,'L');
			$this->Cell(25,5,date_format($this->user->afip_information->inicio_actividades, 'd/m/Y'),0,0,'L');
		}
	}

	function printCommerceLines() {
		$this->SetLineWidth(.3);
		// Abajo
		$this->Line(5, 60, 205, 60);
		// Izquierda
		$this->Line(5, 5, 5, 60);
		// Derecha
		$this->Line(205, 5, 205, 60);
		// Centro
		$this->Line(105, 31, 105, 60);
	}

	function printClientLines() {
		$this->SetLineWidth(.3);
		// Arriba
		$this->Line(5, 62, 205, 62);
		// Abajo
		$this->Line(5, 83, 205, 83);
		// Izquierda
		$this->Line(5, 62, 5, 83);
		// Derecha
		$this->Line(205, 62, 205, 83);
	}

	function printTableHeader() {
		$this->SetX(5);
		$this->SetFont('Arial', 'B', 9, 'L');
		$this->Cell($this->widths['codigo'], 5, 'Código', 1, 0, 'L');
		$this->Cell($this->widths['producto'], 5, 'Producto / Servicio', 1, 0, 'L');
		$this->Cell($this->widths['cantidad'], 5, 'Cantidad', 1, 0, 'C');
		$this->Cell($this->widths['unidad_medida'], 5, 'U.medida', 1, 0, 'L');
		$this->Cell($this->widths['precio_unitario'], 5, 'Precio Unit', 1, 0, 'C');
		$this->Cell($this->widths['bonif'], 5, '% Bonif', 1, 0, 'L');
		$this->Cell($this->widths['subtotal'], 5, 'Subtotal', 1, 0, 'C');
		if ($this->sale->afip_ticket->cbte_tipo == 1) {
			$this->Cell($this->widths['iva'], 5, 'IVA', 1, 0, 'C');
			$this->Cell($this->widths['subtotal_con_iva'], 5, 'Subtotal c/IVA', 1, 0, 'C');
		}

		// Se dibuja la linea celeste que separa el thead del tbody
		$this->SetLineWidth(.6);
		// $this->SetDrawColor(100, 174, 238);
		// $this->Line(5, $finish_y, 206, $finish_y);
		$this->SetY(90);
	}

	function getPuntoVenta() {
		$letras_faltantes = 5 - strlen($this->sale->afip_ticket->punto_venta);
		$punto_venta = '';
		for ($i=0; $i < $letras_faltantes; $i++) { 
			$punto_venta .= '0'; 
		}
		$punto_venta  .= $this->sale->afip_ticket->punto_venta;
		return $punto_venta;
	}

	function getNumCbte() {
		$letras_faltantes = 8 - strlen($this->sale->afip_ticket->cbte_numero);
		$cbte_numero = '';
		for ($i=0; $i < $letras_faltantes; $i++) { 
			$cbte_numero .= '0'; 
		}
		$cbte_numero  .= $this->sale->afip_ticket->cbte_numero;
		return $cbte_numero;
	}

	function __Footer() {
		$this->SetFont('Arial', '', 11);
		$this->AliasNbPages();
		$this->SetY(-30);
		// $this->Write(5,'Hoja '.$this->num_PageNo().'/{nb}');
	}
}