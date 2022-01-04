<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use App\Client;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Sale;
require(__DIR__.'/../fpdf/AlphaPDF.php');
use AlphaPDF;

class PricesListsPdf extends AlphaPDF {

	function __construct($prices_list) {
		parent::__construct('P','mm','A4');
		$this->Y = 0;
		$this->prices_list = $prices_list;
		$this->AddPage();
		$this->printArticles();
        $this->Output();
        exit;
	}

	function printLogo() {
		$this->SetAlpha(0.4);
        $this->Image(public_path().'/logo.png', 3, 5, 20);
		$this->SetAlpha(1);
	}

	function printDatePageNo() {
		$this->setY(5);
		$this->setX(180);
		$this->setFont('Arial', '', 11);
		$this->Cell(20,5,date('d/m/Y'),0,0,'L');
		$this->setY(10);
		$this->setX(180);
		$this->Cell(20,5,'Página '.$this->PageNo(),0,0,'L');
	}

	function printTitle() {
		$this->setY(0);
		$this->setX(0);
		$this->setFont('Arial', '', 16);
		$this->Cell(210,20, $this->prices_list->name,'B',0,'C');
	}

	function printTableHeader() {
		$this->setY(20);
		$this->setX(0);
		$this->setFont('Arial', '', 12);
		$this->Cell(100,7,'Foto','BR',0,'C');
		$this->Cell(75,7,'Nombre','BR',0,'C');
		$this->Cell(35,7,'Precio','B',0,'C');
	}

	function printArticles() {
		$this->Y = 27;
		$this->setFont('Arial', '', 12);
		foreach ($this->prices_list->articles as $article) {
			$image = ArticleHelper::getFirstImage($article);
			if ((!is_null($image) && $this->Y > 207) || (is_null($image) && $this->Y > 288)) {
				$this->AddPage();
				$this->Y = 27;
			}
			$this->setY($this->Y);
			if (!is_null($image)) {
            	$this->Image($image, 10, $this->y+5, 80);
            	$y_name_price = $this->Y;
            	$y_name_price += 40;
            	$this->setY($y_name_price);
            	$this->Y += 90;
			} else {
				$this->setX(0);
				$this->Cell(100,10, 'Sin foto',0,0,'C');
            	$this->Y += 10;
			}
			$this->setX(100);
			$this->Cell(75,10,ArticleHelper::getShortName($article->name, 37),0,0,'C');
			$this->Cell(35,10,ArticleHelper::price($article->price),0,0,'C');
			$this->Line(0, $this->Y, 210, $this->Y);
		}
	}

	function Header() {
		$this->printLogo();
		$this->printDatePageNo();
		$this->printTitle();
		$this->printTableHeader();
	}
}

