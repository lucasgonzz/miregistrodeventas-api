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

class Header extends Index {

	function __construct($afip_ticket) {
		// parent::__construct();
		// $this->SetAutoPageBreak(false);
		$this->afip_ticket = $afip_ticket;
	}

	function print() {
		$this->SetXY(5, 5);
		if ($this->afip_ticket) {
			$this->printTicketCommerceInfo();
			$this->printClientInfo();
			$this->SetY(85);
			$this->printTableHeader(90);
		} else {
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
			$this->printTableHeader(34);
		}
	}

	function printTicketCommerceInfo() {
		$this->SetFont('Arial', 'B', 14, 'C');
		$this->Cell(200,10,'ORIGINAL','T-B',0,'C');
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
		$this->Cell(50,7,'IVA '.$this->sale->afip_ticket->iva_cliente,0,0,'L');
		// $this->Cell(50,7,'IVA '.$this->sale->afip_ticket->iva_cliente,0,0,'L');
		// Iva
		$this->SetY(76);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(32,7,'Condición de venta:',0,0,'L');
		$this->SetFont('Arial', '', 8);
		$this->Cell(50,7,'Contado',0,0,'L');
		// Razon social
		$this->SetY(62);
		$this->SetX(80);
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(47,7,'Apellido y Nombre / Razón Social:',0,0,'L');
		$this->SetFont('Arial', '', 8);
		$this->Cell(60,7,$this->sale->client->razon_social,0,0,'L');
		// $this->Cell(50,7,$this->sale->client->razon_social,0,0,'L');
		// Domicilio
		$this->SetY(69);
		$this->SetX(97);
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(30,7,'Domicilio Comercial:',0,0,'L');
		$this->SetFont('Arial', '', 8);
		// $this->Cell(60,7,'Ruta 11 y Av. pte iillia - Gualeguay, Entre Rios',0,0,'L');
		$this->Cell(60,7,$this->sale->client->address,0,0,'L');
		$this->printClientLines();
	}

	function printCbteTipo() {
		$this->SetY(15);
		$this->SetX(97);
		$this->SetFont('Arial', 'B', 20);
		$this->Cell(16,16,'A',1,0,'C');
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
		$this->Cell(50,10,Auth()->user()->company_name,0,0,'L');
		// Domicilio
		$this->SetY(40);
		$this->SetX(6);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(35,10,'Domicilio Comercial:',0,0,'L');
		$this->SetFont('Arial', '', 9);
		$this->Cell(50,10,Auth()->user()->address,0,0,'L');
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
		$this->SetY(55);
		$this->SetX(118);
		$this->SetFont('Arial', 'B', 9);
		$this->Cell(52,5,'Fecha de Inicio de Actividades:',0,0,'L');
		$this->Cell(25,5,date_format(Auth()->user()->inicio_actividades, 'd/m/Y'),0,0,'L');
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

	function printTableHeader($finish_y) {
		if ($this->afip_ticket) {
			$this->SetX(5);
			$this->SetFont('Arial', 'B', 9, 'L');
			$this->Cell(23, 5, 'Código', 1, 0, 'L');
			$this->Cell(40, 5, 'Producto / Servicio', 1, 0, 'L');
			$this->Cell(17, 5, 'Cantidad', 1, 0, 'C');
			$this->Cell(20, 5, 'U. medida', 1, 0, 'L');
			$this->Cell(20, 5, 'Precio Unit', 1, 0, 'C');
			$this->Cell(15, 5, '% Bonif', 1, 0, 'L');
			$this->Cell(20, 5, 'Subtotal', 1, 0, 'C');
			$this->Cell(20, 5, 'Alicuto IVA', 1, 0, 'C');
			$this->Cell(25, 5, 'Subtotal c/IVA', 1, 0, 'C');

			// Se dibuja la linea celeste que separa el thead del tbody
			$this->SetLineWidth(.6);
			// $this->SetDrawColor(100, 174, 238);
			// $this->Line(5, $finish_y, 206, $finish_y);
			$this->SetY($finish_y);

		} else {
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
			$this->Line($this->margins, $finish_y, $this->margins+array_sum($this->widths), $finish_y);
			$this->SetY($finish_y);
		}
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
}