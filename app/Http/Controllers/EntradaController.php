<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Http\Controllers\Helpers\Numbers;
use fpdf;
require(__DIR__.'/fpdf/fpdf.php');

class EntradaController extends fpdf {

    function entrada() {
        parent::__construct();
        $this->SetAutoPageBreak(true, 1);
        $this->AddPage();
        $this->SetFont('Helvetica', 'B', 18, 'C');
        $this->SetTextColor(255,255,255);
        $cantidad_entradas = 601;
        $x = 0;
        $y = 0;
        $entradas_imprimidas = 0;
        for ($i=300; $i < $cantidad_entradas; $i++) {
            $entradas_imprimidas++;
            $this->Image(public_path('entrada.jpeg'), $x, $y, 100);
            $this->SetXY($x+5, $y +73);
            $this->Cell(50,5,'N° '.$i,0,0,'L');
            if ($x == 0) {
                $x = 100;
            } else {
                $x = 0;
                $y += 99;
            }
            if ($entradas_imprimidas == 6) {
                $this->AddPage();
                $x = 0;
                $y = 0;
                $entradas_imprimidas = 0;
            }
        }
        $this->Output();
        
    }
}


