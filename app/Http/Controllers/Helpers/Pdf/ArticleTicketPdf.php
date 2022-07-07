<?php

namespace App\Http\Controllers\Helpers\Pdf;

use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\UserHelper;
use fpdf;
require(__DIR__.'/../../fpdf/fpdf.php');

class ArticleTicketPdf extends fpdf {

	function __construct($articles) {
		$this->line_height = 7;
		$this->user = UserHelper::getFullModel();
		$this->articles = $articles;

		parent::__construct();
		$this->SetAutoPageBreak(false);
		$this->b = 'LR';

		$this->AddPage();
		$this->articles();
        $this->Output();
        exit;
	}

	function articles() {
		$x = 0;
		$max_y = 1;
		$last_max_y = 1;
		$this->y = 1;
		foreach ($this->articles as $article) {
			$this->x = $x;
			$this->SetFont('Arial', 'B', 12);

			$this->MultiCell(69, $this->line_height, $article->name, $this->b.'TB', 'L', 0);
			$this->x = $x;
			if ($this->y > $max_y) {
				$max_y = $this->y;
			}

			$this->SetFont('Arial', '', 14);
			$this->Cell(20, $this->line_height, 'Precio', 'L', 0, 'L');
			$this->Cell(49, $this->line_height, '$'.Numbers::Price($article->price), 'R', 0, 'R');

			$this->x = $x;
			$this->y += $this->line_height;
			$this->SetFont('Arial', '', 10);
			$this->Cell(20, $this->line_height, 'Cod bar', 'L', 0, 'L');
			$this->Cell(49, $this->line_height, $article->bar_code, 'R', 0, 'R');

			$this->x = $x;
			$this->y += $this->line_height;
			$this->Cell(20, $this->line_height, 'Cod prov', 'L', 0, 'L');
			$this->Cell(49, $this->line_height, $article->provider_code, 'R', 0, 'R');

			$this->x = $x;
			$this->y += $this->line_height;
			$this->Cell(20, $this->line_height, 'Num ', 'LB', 0, 'L');
			$this->Cell(49, $this->line_height, $this->y, 'BR', 0, 'R');

			if ($x < 140) {
				$x += 70;
				$this->y = $last_max_y;
			} else {
				if ($this->y >= 220) {
					$this->AddPage();
					$x = 0;
					$this->y = 1;
					$last_max_y = $this->y;
					$max_y = 0;
				} else {
					$x = 0;
					$max_y++;
					$this->y = $max_y + ($this->line_height * 4);
					$last_max_y = $this->y;
					$max_y = 0;
				}
			}
		}
	}


}