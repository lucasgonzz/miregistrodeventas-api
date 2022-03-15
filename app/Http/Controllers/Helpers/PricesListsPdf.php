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
		$this->SetAutoPageBreak(false);
		$this->Y = 0;
		$this->prices_list = $prices_list;
		$this->maximo_letras = 38;
		$this->lineas_impresas = 0;
		$this->line_height = 7;
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
		$this->Cell(80,7,'Foto / Descripción','BR',0,'C');
		$this->Cell(95,7,'Nombre','BR',0,'C');
		$this->Cell(35,7,'Precio','B',0,'C');
	}

	function printArticles() {
		$this->Y = 27;
		$this->setFont('Arial', '', 12);
		foreach ($this->prices_list->articles as $article) {
			$this->article = $article;
			$this->printArticle();
		}
	}

	function printArticle() {
		$next_y = $this->getNextY();
		if ($next_y < 290) {
			$image = ArticleHelper::getFirstImage($this->article);
			$image = null;
	        $this->setY($this->Y);
			if (!is_null($image)) {
	        	$this->Image($image, 10, $this->Y+5, 60);
		        $this->SetX(80);
				$this->Cell(95,$this->getLineHeight(),ArticleHelper::getShortName($this->article->name, 47),'L',0,'C');
				$this->Cell(35,$this->getLineHeight(),ArticleHelper::price($this->getLineHeight()),'L',0,'C');
	        	$this->Y += 80;
			} else {
				$this->setX(0);
    			$description_text = $this->getDescriptionText();
		        // $this->MultiCell(100,$this->line_height,$this->article->id.'. Y= '.$this->Y.'. next_y= '.$next_y,0,'L', false);
		        $this->MultiCell(80,$this->line_height,$description_text,0,'L', false);
		        $this->SetY($this->Y);
				$this->setX(80);
				$this->Cell(95,$this->getLineHeight(),ArticleHelper::getShortName($this->article->name, 37),'L',0,'C');
				$this->Cell(35,$this->getLineHeight(),ArticleHelper::price($this->article->price),'L',0,'C');
	        	$this->Y += $this->getLineHeight();
			}
	        $this->printLine();
		} else {
			// dd($this->article->id);
			$this->AddPage();
            $this->Y = 27;
            $this->printArticle();
		}
	}
	
    function printLine() {
		$this->SetDrawColor(0,0,0);
		$this->Line(0, $this->Y, 210, $this->Y);
    }

    function getNextY() {
    	return $this->Y + $this->getLineHeight();
    }

    function getLineHeight() {
		$image = ArticleHelper::getFirstImage($this->article);
		$image = null;
		if (!is_null($image)) {
	    	return 80;
		} else {
	    	$lines = 1;
	    	$description_text = $this->getDescriptionText();
	    	$letras = strlen($description_text);
	    	while ($letras > $this->maximo_letras) {
	    		$lines++;
	    		$letras -= $this->maximo_letras;
	    		$this->lineas_impresas++;
	    	}
	    	if ($lines > 10) {
	    		$lines++;
	    	}
	    	return $this->line_height * $lines;
		}
    }

    function getDescriptionText() {
    	$description_text = '';
    	foreach ($this->article->descriptions as $description) {
    		if (!is_null($description->title)) {
    			$description_text .= $description->title.': ';
    		}
    		if (substr($description_text, -1) != '.') {
    			$description_text .= $description->content.'. ';
    		} else {
    			$description_text .= $description->content.' ';
    		}
    	}
    	return $description_text;
    }

	function Header() {
		$this->printLogo();
		$this->printDatePageNo();
		$this->printTitle();
		$this->printTableHeader();
	}
}

