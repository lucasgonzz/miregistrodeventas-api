<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Sale;
use App\Client;

require(__DIR__.'/../fpdf/fpdf.php');
use fpdf;

class PdfPrintArticles extends fpdf {

	function __construct($orientation, $columns_string, $header) {
		if ($orientation == 'normal') {
			parent::__construct('P','mm','A4');
		} else {
			parent::__construct('L','mm','A4');
		}
		$this->orientation = $orientation;

    	if (!is_null($header)) {
    		$header = explode('-', $header);
    	}
		$this->header = $header;

    	$columns_ = explode('-', $columns_string);
    	$columns = [];
    	foreach ($columns_ as $column) {
    		switch ($column) {
    			case 'bar_code':
    				$columns['bar_code'] = 45;
    				break;
    			case 'name':
    				$columns['name'] = 50;
    				break;
    			case 'cost':
    				$columns['cost'] = 20;
    				break;
    			case 'price':
    				$columns['price'] = 20;
    				break;
    			case 'previus_price':
    				$columns['previus_price'] = 20;
    				break;
    			case 'stock':
    				$columns['stock'] = 25;
    				break;
    			case 'created_at':
    				$columns['created_at'] = 25;
    				break;
    			case 'updated_at':
    				$columns['updated_at'] = 25;
    				break;
    		}
    	}
		$this->columns = $columns;

		// Margenes
		$this->width = 0;
		foreach ($this->columns as $column => $w) {
			$this->width += $w;
		}
		if ($this->orientation == 'normal') {
			$this->margins = (210 - $this->width) / 2;
		} else {
			$this->margins = (297 - $this->width) / 2;
		}
	}

	function printArticles($articles) {
		$this->setFont('Arial', '', 12);
		foreach ($articles as $article) {
			$this->SetX($this->margins);
			foreach ($this->columns as $column => $w) {
				if ($column=='created_at' || $column=='updated_at') {
					$this->Cell($w,10,date_format($article->{$column}, 'd/m/y'),'B',0,'C');
				} else {
					$align = 'L';
					if ($column=='stock') {
						$align = 'R';
						$this->Cell($w,10,$this->stock($article),'B',0,$align);
					} else {
						if ($column=='name') {
							if (strlen($article->{$column}) > 19) {
								$article->{$column} = substr($article->{$column}, 0, 19) . '..';
							}
						}
						if ($column=='price' || $column=='cost' || $column=='previus_price') {
							$align = 'R';
							$article->{$column} = $this->price($article->{$column});
						}
						$this->Cell($w,10,$article->{$column},'B',0,$align);
					}
				}
			}
			$this->Ln();
		}
	}

	function stock($article) {
		if (is_null($article->stock)) {
			return '-';
		} else {
			if ($article->uncontable == 0) {
				return substr($article->stock, 0, -3);
			} else {
				if ($article->measurement == 'gramo') {
					$measurement= 'gr';
				} else {
					$measurement= 'kg';
				}
				if (strripos($article->stock, '.00') != false) {
					return substr($article->stock, 0, -3)." $measurement";
				} else {
					return "$article->stock $measurement";
				}
			}
		}
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

	function Header() {

		$this->SetXY(10, 10);
		// dd($this->header);

		// Si el nombre del negocio es verdaderop se escribe
		// y se pone el cursor 1cm mas abajo
		if (in_array('company_name', $this->header)) {
			$this->SetFont('Arial', 'B', 18, 'C');
			$this->Cell(100,5,Auth()->user()->company_name,0,0,'L');
			$this->SetY(35);
		}

		// Se escribe la fecha
		if (in_array('date', $this->header)) {
			$this->SetFont('Arial','',11);
			$this->SetXY(150, 10);
			$this->Cell(50,5,'Fecha: '.date('d/m/y'),0,0,'L');
			$this->SetXY(150, 20);
			$this->Cell(50,5,'Hora: '.date('H:m'),0,0,'L');
			$this->SetLineWidth(.6);
			$this->Line(5,27,205,27);

			// Se baja 1cm abajo
			$this->SetY(32);
		}

		// Se empieza a escribir la cabecera
		$this->setXY($this->margins, 35);
		$this->SetFont('Arial', 'B', 14, 'L');

		foreach ($this->columns as $column => $w) {
			switch ($column) {
				case 'bar_code':
					$column_es = 'Código';
					break;
				case 'name':
					$column_es = 'Nombre';
					break;
				case 'cost':
					$column_es = 'Costo';
					break;
				case 'price':
					$column_es = 'Precio';
					break;
				case 'stock':
					$column_es = 'Stock';
					break;
				case 'previus_price':
					$column_es = 'P. Anterior';
					break;
				case 'created_at':
					$column_es = 'Ingresado';
					break;
				case 'updated_at':
					$column_es = 'Actualizado';
					break;
			}
			$this->Cell($w,5,$column_es,0,0,'C');
			$this->Line($this->margins, 43, $this->margins+$this->width, 43);
		}
		$this->Ln(10);
	}

	function Footer() {
		$this->AliasNbPages();
		$this->SetY(-15);
		$this->SetX(10);
		$this->setFont('Arial', '', 11);
		$this->Write(5,$this->PageNo().'/{nb}');
	}
}

