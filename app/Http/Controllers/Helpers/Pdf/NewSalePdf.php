<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\BudgetHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class NewSalePdf extends fpdf {

	function __construct($sales) {
		parent::__construct();
		$this->SetAutoPageBreak(true, 50);
		$this->start_x = 5;
		$this->b = 0;
		$this->line_height = 7;
		$this->table_header_line_height = 10;
		
		$this->user = UserHelper::getFullModel();
		$this->sales = $sales;

		$this->initWiths();

		$this->sales();
        $this->Output();
        exit;
	}

	function initWiths() {
		$this->widths = [
			'#' 		=> 5,
			'bar_code' 	=> 40,
			'name' 		=> 75,
			'price' 	=> 25,
			'amount' 	=> 15,
			'discount' 	=> 13,
			'sub_total' => 27,
		]; 
	}

	function Header() {
		$this->logo();
		$this->clientInfo();
		$this->numSale();
		$this->commerceInfo();
		$this->tableHeader();
	}

	function Footer() {
		$this->total();
		$this->discounts();
		$this->afipTicket();
		$this->comerciocityInfo();
	}

	function sales() {
		foreach ($this->sales as $sale) {
			$this->sale = $sale;
			$this->AddPage();
			$this->items();
		}
	}

	function items() {
		$this->SetFont('Arial', '', 10);
		$this->SetDrawColor(150,150,150);
		$this->SetLineWidth(.4);
		$index = 1;
		// for ($i=0; $i < 30; $i++) { 
			foreach ($this->sale->articles as $article) {
				$this->printItem($index, $article);
				$index++;
			}
			foreach ($this->sale->services as $service) {
				$this->printItem($index, $service);
				$index++;
			}
		// }
	}

	function printItem($index, $item) {
		$this->Cell(
			$this->widths['#'], 
			$this->line_height, 
			$index, 
			$this->b, 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['bar_code'], 
			$this->line_height, 
			$item->bar_code, 
			$this->b, 
			0, 
			'C'
		);
		$y_1 = $this->y;
	    $this->MultiCell(
			$this->widths['name'], 
			$this->line_height, 
			$item->name, 
	    	$this->b, 
	    	'L', 
	    	false
	    );
	    $y_2 = $this->y;
	    $this->y = $y_1;
	    $this->x = $this->start_x + $this->widths['#'] + $this->widths['bar_code'] + $this->widths['name'];
		$this->Cell(
			$this->widths['price'], 
			$this->line_height, 
			'$'.$item->pivot->price, 
			$this->b, 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['amount'], 
			$this->line_height, 
			$item->pivot->amount, 
			$this->b, 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['discount'], 
			$this->line_height, 
			$item->pivot->discount, 
			$this->b, 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['sub_total'], 
			$this->line_height, 
			'$'.SaleHelper::getTotalItem($item), 
			$this->b, 
			0, 
			'C'
		);
		$this->x = $this->start_x;
		$this->y = $y_2;
		$this->Line($this->start_x, $this->y, 210-$this->start_x, $this->y);
	}

	function tableHeader() {
		$this->x = $this->start_x;
		$this->y = 60;
		$this->SetFont('Arial', 'B', 12);
		$this->SetLineWidth(.6);
		$this->SetDrawColor(150,150,150);
		$this->Cell(
			$this->widths['#'], 
			$this->table_header_line_height, 
			'#', 
			'TB', 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['bar_code'], 
			$this->table_header_line_height, 
			'Codigo', 
			'TB', 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['name'], 
			$this->table_header_line_height, 
			'Nombre', 
			'TB', 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['price'], 
			$this->table_header_line_height, 
			'Precio', 
			'TB', 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['amount'], 
			$this->table_header_line_height, 
			'Cant', 
			'TB', 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['discount'], 
			$this->table_header_line_height, 
			'Des', 
			'TB', 
			0, 
			'C'
		);
		$this->Cell(
			$this->widths['sub_total'], 
			$this->table_header_line_height, 
			'SubTotal', 
			'TB', 
			0, 
			'C'
		);
		$this->x = $this->start_x;
		$this->y += $this->table_header_line_height;
	}

	function logo() {
        if (!is_null($this->user->image_url)) {
        	$this->Image(ImageHelper::image($this->user), 5, 5, 0, 27);
        }
	}

	function numSale() {
		$this->SetFont('Arial', 'B', 14);
		$this->x = 105;
		$this->y = 0;

		// Numero
		$this->Cell(50, 10, 'N° '.$this->sale->num_sale, $this->b, 0, 'L');
		$this->Cell(50, 10, date_format($this->sale->created_at, 'd/m/Y'), $this->b, 0, 'R');
		$this->y += 10;
	}

	function commerceInfo() {
		// Razon social
		if (!is_null($this->user->afip_information)) {
			if (!is_null($this->user->afip_information->razon_social)) {
				$this->x = 105;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(25, 5, 'Razon social: ', $this->b, 0, 'L');

				$this->SetFont('Arial', '', 10);
				$this->Cell(75, 5, $this->user->afip_information->razon_social, $this->b, 0, 'L');
			}

			// Condicion frente al iva
			if (!is_null($this->user->afip_information->iva_condition)) {
				$this->x = 105;
				$this->y += 5;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(41, 5, 'Condicion frente al iva: ', $this->b, 0, 'L');

				$this->SetFont('Arial', '', 10);
				$this->Cell(59, 5, $this->user->afip_information->iva_condition->name, $this->b, 0, 'L');
			}

			// Cuit
			if (!is_null($this->user->afip_information->cuit)) {
				$this->x = 105;
				$this->y += 5;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(10, 5, 'Cuit:', $this->b, 0, 'L');
				$this->SetFont('Arial', '', 10);
				$this->Cell(90, 5, $this->user->afip_information->cuit, $this->b, 0, 'L');
			}

			// Ingresos Brutos
			if (!is_null($this->user->afip_information->ingresos_brutos)) {
				$this->x = 105;
				$this->y += 5;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(30, 5, 'Ingresos Brutos:', $this->b, 0, 'L');
				$this->SetFont('Arial', '', 10);
				$this->Cell(70, 5, $this->user->afip_information->ingresos_brutos, $this->b, 0, 'L');
			}

			// Inicio Actividades
			if (!is_null($this->user->afip_information->inicio_actividades)) {
				$this->x = 105;
				$this->y += 5;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(33, 5, 'Inicio Actividades:', $this->b, 0, 'L');
				$this->SetFont('Arial', '', 10);
				$this->Cell(67, 5, date_format($this->user->afip_information->inicio_actividades, 'd/m/Y'), $this->b, 0, 'L');
			}
		}

		// Direccion
		$address = null;
		if (count($this->user->addresses) >= 1) {
			$address = $this->user->addresses[0];
		}
		if (!is_null($address)) {
			$this->x = 105;
			$this->y += 5;
			$this->SetFont('Arial', 'B', 10);
			$this->Cell(20, 5, 'Direccion: ', $this->b, 0, 'L');

			$address_text = "{$address->street} {$address->street_number}, {$address->city}, {$address->province}";
			$this->SetFont('Arial', '', 10);
			$this->Cell(80, 5, $address_text, $this->b, 0, 'L');
		}

		// Telefono
		if (!is_null($this->user->phone)) {
			$this->x = 105;
			$this->y += 5;
			$this->SetFont('Arial', 'B', 10);
			$this->Cell(18, 5, 'Telefono: ', $this->b, 0, 'L');

			$this->SetFont('Arial', '', 10);
			$this->Cell(82, 5, $this->user->phone, $this->b, 0, 'L');
		}

		// Sitio Web
		if (!is_null($this->user->online)) {
			$this->x = 105;
			$this->y += 5;
			$this->SetFont('Arial', 'B', 10);
			$this->Cell(20, 5, 'Sitio Web: ', $this->b, 0, 'L');

			$this->SetFont('Arial', '', 10);
			$this->Cell(80, 5, $this->user->online, $this->b, 0, 'L');
		}

		// Correo
		$this->x = 105;
		$this->y += 5;
		$this->SetFont('Arial', 'B', 10);
		$this->Cell(12, 5, 'Email:', $this->b, 0, 'L');
		$this->SetFont('Arial', '', 10);
		$this->Cell(88, 5, $this->user->email, $this->b, 0, 'L');
		$this->y += 10;
	}

	function clientInfo() {
		if ($this->sale->client) {
			$this->SetFont('Arial', '', 10);
			$this->x = $this->start_x;
			$this->y = 35;
			$this->SetFont('Arial', 'B', 10);
			$this->Cell(15, 5, 'Cliente:', $this->b, 0, 'L');
			$this->SetFont('Arial', '', 10);
			$this->Cell(85, 5, $this->sale->client->name, $this->b, 0, 'L');

			if ($this->sale->client->address != '') {
				$this->y += 5;
				$this->x = $this->start_x;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(20, 5, 'Direccion:', $this->b, 0, 'L');
				$this->SetFont('Arial', '', 10);
				$this->Cell(80, 5, $this->sale->client->address, $this->b, 0, 'L');
			} 
			if (!is_null($this->sale->client->iva_condition)) {
				$this->y += 5;
				$this->x = $this->start_x;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(23, 5, 'Con. de IVA:', $this->b, 0, 'L');
				$this->SetFont('Arial', '', 10);
				$this->Cell(77, 5, $this->sale->client->iva_condition->name, $this->b, 0, 'L');
			}
			if ($this->sale->client->cuit != '') {
				$this->y += 5;
				$this->x = $this->start_x;
				$this->SetFont('Arial', 'B', 10);
				$this->Cell(12, 5, 'CUIT:', $this->b, 0, 'L');
				$this->SetFont('Arial', '', 10);
				$this->Cell(88, 5, $this->sale->client->cuit, $this->b, 0, 'L');
			}
			// $this->y += 5;
		}
	}

	function total() {
	    $this->x = $this->start_x;
	    $this->y = 247;
	    $this->SetFont('Arial', 'B', 12);
		$this->Cell(
			100,
			10,
			'Total: $'. Numbers::price(SaleHelper::getTotalSale($this->sale, false)),
			$this->b,
			0,
			'L'
		);
		$this->y += 10;
	}

	function discounts() {
		if (count($this->sale->discounts) >= 1) {
		    $this->x = $this->start_x;
		    $this->SetFont('Arial', '', 9);
		    $total_sale = SaleHelper::getTotalSale($this->sale, false);
		    foreach ($this->sale->discounts as $discount) {
		    	$text = '-'.$discount->pivot->percentage.'% '.$discount->name;
		    	$descuento = $total_sale * floatval($discount->pivot->percentage) / 100;
		    	$total_sale = Numbers::redondear($total_sale - $descuento);
		    	$text .= ' = $'.$total_sale;
				$this->Cell(
					50, 
					5, 
					$text, 
					$this->b, 
					0, 
					'L'
				);
				if ($this->x > 55) {
					$this->x = $this->start_x;
					$this->y += 5;
				}
		    }
		    if ($this->x == 55) {
				$this->x = $this->start_x;
				$this->y += 5;
		    }
	    	$this->SetFont('Arial', 'B', 12);
		    $this->Cell(
				50, 
				10, 
				'Total: $'.SaleHelper::getTotalSale($this->sale), 
				$this->b, 
				0, 
				'L'
			);
		}
	}

	function afipTicket() {
		if (!is_null($this->sale->afip_ticket)) {
			dd($this->sale->afip_ticket);
		}
	}

	function comerciocityInfo() {
	    $this->y = 290;
	    $this->x = $this->start_x;
	    $this->SetFont('Arial', '', 10);
		$this->Cell(200, 5, 'Comprobante creado con el sistema de control de stock ComercioCity - comerciocity.com', $this->b, 0, 'C');
	}

	function getHeight($product) {
    	$lines = 1;
    	$letras = strlen($product->name);
    	while ($letras > 41) {
    		$lines++;
    		$letras -= 41;
    	}
    	return $this->line_height * $lines;
	}

}