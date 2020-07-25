<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// require('fpdf/fpdf.php');
use fpdf;
use App\Article;
use App\Sale;
use App\Http\Controllers\Helpers\PdfSaleClient;
use App\Http\Controllers\Helpers\PdfSaleCommerce;
use App\Http\Controllers\Helpers\PdfPrintArticles;

class PdfController extends Controller
{

    function getArticleOwnerId() {
        $user = Auth()->user();
        if (is_null($user->owner_id)) {
            return $user->id;
        } else {
            return $user->owner_id;
        }
    }

	function barCodeDirectory() {
        if(!is_dir(storage_path()."/barcodes/".$this->getArticleOwnerId())) {
            mkdir(storage_path().'/barcodes/'.$this->getArticleOwnerId());
        }
	}

	function printTicket($articles_id, $company_name) {
		require('barcode/barcode.php');
		require('fpdf/fpdf.php');
		$user = Auth()->user();
		$company_name = (bool)$company_name;
		$pdf = new fpdf();
		$pdf->addPage();
        $x = 10;
        $y = 10;
        $largo_ticket = 0;
        $articles_id = explode('-', $articles_id);
        foreach ($articles_id as $article_id) {
        	$article = Article::find($article_id);
			$bar_code = $article->bar_code;
			$price = $article->price;
			$offer_price = $article->offer_price;
			$name = $article->name;

			// Se crea el directorio si no existe
			$this->barCodeDirectory();

	        $widths = [];
			// Se crea la imagen del codigo de barras si existe
	        if (!is_null($bar_code)) {
		        barcode(storage_path().'/barcodes/'.$this->getArticleOwnerId().'/'.$bar_code.'.png', 
		                $bar_code, 20, 'horizontal', 'code128', 1);
		        $width_image = getimagesize(storage_path().'/barcodes/'.$this->getArticleOwnerId().'/'.$bar_code.'.png');
		        $widths['bar_code'] = $width_image[0] * 50 / 200;
		        $bar_code_printed = true;
	        }

	        // Se obtienen los largos del nombre, del precio, del preci de oferta
	        // y del nombre de la compania
	        $widths['name'] = strlen($name)*2.5;
	        $widths['price'] = (strlen($this->price_ticket($price))+1)*5;
	        if (!is_null($offer_price)) {
	        	$offer_price_width = (strlen($this->price_ticket($offer_price))+1)*5;
	        	$old_price_width = (strlen($this->price_ticket($price))+1)*3;
	        	$widths['offer_price'] = $offer_price_width;
	        	$widths['old_price'] = $old_price_width;
	        	$widths['offer_price_old_price'] = $offer_price_width + $old_price_width;
	        }
	        $widths['company_name'] = strlen($user->company_name)*1.7;

	        // Se obtiene el valor mas largo
	        // entre el nombre, el precio y el codigo de barras
	        $largo_ticket = max(array(
	        						$widths['name'], 
	        						$widths['price'], 
	        						isset($widths['bar_code']) ? $widths['bar_code'] : null, 
	        						isset($widths['offer_price_old_price']) ? $widths['offer_price_old_price'] : null, 
	        						isset($widths['company_name']) ? $widths['company_name'] : null));

	        /* ----------------------------------------------------------------------------
				* Se le resta el largo del futuro ticket a lo que queda de pagina 
				* Si lo que va de x es mayor que queda de pagina se baja de linea
				* Si se pinto un codigo de barra se baja mas que si no se pinto
				* Si se pinto el nombre de la compania tambien
	        ----------------------------------------------------------------------------  */
	        if ($x > 205-$largo_ticket) {
	        	$x = 10;
	        	$y += 40;
	        }
        	if ($y + 40 > 280) {
        		// dd($name);
        		$y = 10;
        		$pdf->AddPage();
        	} 


	        /* ----------------------------------------------------------------------------
				* Se dibuja la linea de arriba
				* Se le suman cuatro porque tiene 2 de espacio entre los bordes
	        ----------------------------------------------------------------------------  */
        	$pdf->Line($x, $y, $x+$largo_ticket+4, $y);

	        /* ----------------------------------------------------------------------------
				* Se dibuja la linea de la izquierda
				* Si no tiene codigo de barras se baja menos
				* Si se pinta el nombre de la compania se baja mas
				* Si se pinto el nombre de la compania tambien
	        ----------------------------------------------------------------------------  */
	        $alto_linea = 22;
	        if (!is_null($bar_code)) {
	        	$alto_linea += 10;
	        }
	        if ($company_name) {
	        	$alto_linea += 4;
	        }
	        $pdf->Line($x, $y, $x, $y+$alto_linea);

	        // Se aumenta x para que tenga espacio desde los bordes
	        $x += 2;

			// Se escribe el nombre
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('Arial', '', 12);
	        $pdf->SetXY($x, $y);
	        $pdf->Cell($largo_ticket, 7,$name,'B',0,'C');
			
	        /* ----------------------------------------------------------------------------
				* Se escribe el precio
				* Primero el signo de $ (peso)
	        ----------------------------------------------------------------------------  */
	        $pdf->SetXY($x, $y+9);
	        $pdf->Cell(4,4,'$',0,0,'L');

	        /* ----------------------------------------------------------------------------
				* Se tiene precio de oferta se escribe en rojo
				* Se le restan 4 al tamaño de la celda porque 4 es lo que 
				mide el signo de peso
				* Se dibuja el precio y despues se lo tacha
				* Se coloca X en el valor de x mas lo que mide el precio de oferta
				para que desde ahi arranque a dibujarce el precio actual
	        ----------------------------------------------------------------------------  */
			$pdf->SetFont('Arial', '', 28);
			if (!is_null($offer_price)) {
				$pdf->SetTextColor(226, 53, 53);
		        $pdf->Cell($largo_ticket-4,10,$this->price_ticket($offer_price),0,0,'L');

		        // Se dibuja el precio y despues se lo tacha
		        $pdf->SetTextColor(0,0,0);
		        $pdf->SetFont('Arial', '', 12);
		        $pdf->SetXY($x+$widths['offer_price'], $y+9);
		        $pdf->Cell(4,4,'$',0,0,'L');
		        $pdf->SetX($x+$widths['offer_price']+2);
		        $pdf->SetFont('Arial', '', 16);
		        $pdf->Cell($widths['old_price'], 5, $this->price_ticket($price),0,0);
		        $pdf->SetLineWidth(.6);
		        $pdf->Line($x+$widths['offer_price'], $y+10, $x+$widths['offer_price_old_price']+1, $y+12);
			} else {
				$pdf->SetTextColor(0,0,0);
		        $pdf->Cell($largo_ticket-4,10,$this->price_ticket($price),0,0,'L');
			}

	        // Si hay codigo de barra se escribe la imagen
	        if (!is_null($bar_code)) {
		        $pdf->Image(
		        				storage_path().'/barcodes/'.$this->getArticleOwnerId().'/'.$bar_code.'.png',
		        				$x,
		        				$y+20,
		        				$widths['bar_code'],
		        				0,
		        				'PNG'
		        			);
	        }

	        $pdf->SetLineWidth(.3);
	        if ($company_name) {
	        	$pdf->SetFont('Arial', 'I', 10);
	        	$pdf->SetTextColor(0,0,0);
	        	if (!is_null($bar_code)) {
	        		$pdf->SetXY($x, $y+30);
	        		$pdf->Cell($largo_ticket,5,$user->company_name,'T',0);
	        	} else {
	        		$pdf->SetXY($x, $y+20);
	        		$pdf->Cell($largo_ticket,5,$user->company_name,'T',0);
	        	}
	        }

	        // Linea derecha
		    $pdf->Line($x+$largo_ticket+2, $y, $x+$largo_ticket+2, $y+$alto_linea);
		    // Linea de abajo
	        $pdf->Line($x-2, $y+$alto_linea, $x+$largo_ticket+2, $y+$alto_linea);


	        // Se aumenta x con el campo mas largo y se le suman 4
	        // 2 del espacio que hay con la linea izquierda
	        // 2 del espacio que hay con la linea de la derecha
	        // 4 mas para que el proximo este separado
	        $x += $largo_ticket+6;

	        // Cada letra en 26 mide 5
        }
        $pdf->Output();
        exit;
	}

	function price_ticket($price) {
		return number_format($price, 2, ',', '.');
	}

	/* -------------------------------------------------------------------------------
		*
		* Pdf de las ventas
		*
 	-------------------------------------------------------------------------------*/

	function sale_client($company_name, $borders, $sale_id) {
        $pdf = new PdfSaleClient($sale_id, (bool)$company_name, (bool)$borders);
        $pdf->printSale();
	}

	function sale_commerce($company_name, $borders, $sale_id) {
        $pdf = new PdfSaleCommerce($sale_id, (bool)$company_name, (bool)$borders);
        $pdf->printSale();
	}

	/* -------------------------------------------------------------------------------
		*
		* Termina los pdf de las ventas
		*
 	-------------------------------------------------------------------------------*/




	/* -------------------------------------------------------------------------------
		*
		* Imprimir los articulos del listado
		*
 	-------------------------------------------------------------------------------*/

    function articles($columns_string, $articles_ids_string, $orientation, $header = null) {

    	/*
		|-------------------------------------------------------------------
		|	Se consiguen los articulos para imprimir
		|-------------------------------------------------------------------
		|	Si los id == todos se consiguen todos los articulos
		|	Sino se separan los id y se buscan los articulos 1 x 1
		|
    	*/
    	$user = Auth()->user();
    	if ($articles_ids_string == 'todos') {
    		$articles = Article::where('user_id', $this->getArticleOwnerId())
    							->orderBy('id', 'DESC')
    							->get();
    	} else {
    		$articles_ids = explode('-', $articles_ids_string);
	    	foreach ($articles_ids as $id_article) {
	    		$articles[] = Article::where('user_id', $this->getArticleOwnerId())
	    								->where('id', $id_article)
	    								->first();
	    	}
    	}

		$pdf = new PdfPrintArticles($orientation, $columns_string, $header);

		$pdf->AliasNbPages();
		$pdf->addPage();
		$pdf->setFont('Arial', '', 14);
		$pdf->printArticles($articles);
		$pdf->Output();
		exit;
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
}







