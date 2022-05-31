<?php

namespace App\Http\Controllers\Helpers;
use App\Http\Controllers\Helpers\Numbers;
use Illuminate\Support\Facades\Log;

class AfipHelper {

    static function getNumeroComprobante($wsfe, $punto_venta, $cbte_tipo) {
        $pto_vta = [
            'PtoVta'    => $punto_venta,
            'CbteTipo'  => $cbte_tipo
        ];
        $result = $wsfe->FECompUltimoAutorizado($pto_vta);
        return $result->FECompUltimoAutorizadoResult->CbteNro + 1;
    }

    static function getImportes($sale) {
        $items = [];
        $gravado            = 0;
        $neto_no_gravado    = 0;
        $exento             = 0;
        $iva                = 0;
        $ivas = [
            '27' => ['BaseImp' => 0, 'Importe' => 0, 'Id' => 6],
            '21' => ['BaseImp' => 0, 'Importe' => 0, 'Id' => 5],
            '10' => ['BaseImp' => 0, 'Importe' => 0, 'Id' => 4],
            '5'  => ['BaseImp' => 0, 'Importe' => 0, 'Id' => 8],
            '2'  => ['BaseImp' => 0, 'Importe' => 0, 'Id' => 9],
            '0'  => ['BaseImp' => 0, 'Importe' => 0, 'Id' => 3],
        ];
        $subtotal           = 0;
        $total              = 0;
        foreach ($sale->articles as $article) {
            if (Auth()->user()->afip_information->iva_condition->name == 'Responsable inscripto') {
                $gravado                += Self::getImporteGravado($article);
                $exento                 += Self::getImporteIva($article, 'Exento')['Importe'];
                $neto_no_gravado        += Self::getImporteIva($article, 'No Gravado')['Importe'];
                $iva                    += Self::getImporteIva($article);
                $res                    = Self::getImporteIva($article, '27');
                $ivas['27']['Importe']  += $res['Importe'];
                $ivas['27']['BaseImp']  += $res['BaseImp'];
                $res                    = Self::getImporteIva($article, '21');
                $ivas['21']['Importe']  += $res['Importe'];
                $ivas['21']['BaseImp']  += $res['BaseImp'];
                $res                    = Self::getImporteIva($article, '10.5');
                $ivas['10']['Importe']  += $res['Importe'];
                $ivas['10']['BaseImp']  += $res['BaseImp'];
                $res                    = Self::getImporteIva($article, '5');
                $ivas['5']['Importe']  += $res['Importe'];
                $ivas['5']['BaseImp']  += $res['BaseImp'];
                $res                    = Self::getImporteIva($article, '2.5');
                $ivas['2']['Importe']  += $res['Importe'];
                $ivas['2']['BaseImp']  += $res['BaseImp'];
                $res                    = Self::getImporteIva($article, '0');
                $ivas['0']['Importe']  += $res['Importe'];
                $ivas['0']['BaseImp']  += $res['BaseImp'];
            } 
        }
        $gravado = Numbers::redondear($gravado);
        $neto_no_gravado = Numbers::redondear($neto_no_gravado);
        $exento = Numbers::redondear($exento);
        $iva = Numbers::redondear($iva);
        $total = Numbers::redondear($gravado + $neto_no_gravado + $exento + $iva);
        return [
            'gravado'           => $gravado,
            'neto_no_gravado'   => $neto_no_gravado,
            'exento'            => $exento,
            'iva'               => $iva,
            'ivas'              => $ivas,
            // 'subtotal'           => $subtotal,
            'total'             => $total,
        ];
    }

    static function getDocType($slug) {
        $doc_type = [
            'Cuit' => 80,
            'Cuil' => 86,
            'CDI' => 87,
            'LE' => 89,
            'LC' => 90,
            'CI Extranjera' => 91,
            'en trámite' => 92,
            'Acta Nacimiento' => 93,
            'CI Bs. As. RNP' => 95,
            'DNI' => 96,
        ];
        return $doc_type[$slug];
    }

    static function getPriceWithoutIva($article) {
        if (Auth()->user()->afip_information->iva_condition->name == 'Responsable inscripto') {
            if ($article->iva->percentage != 'No Gravado' && $article->iva->percentage != 'Exento') {
                $percentage = floatval($article->iva->percentage);
                if (is_int($percentage)) {
                    $num = floatval('1.'.$article->iva->percentage);
                } else {
                    if (floatval($article->iva->percentage) >= 10) {
                        $num = floatval('1.'.str_replace('.', '', $article->iva->percentage));
                    } else {
                        $num = floatval('1.0'.str_replace('.', '', $article->iva->percentage));
                    }
                }
                $res = $article->pivot->price / $num ;
                return Numbers::redondear($res);
            } else {
                return $article->pivot->price;
            }
        } else if (Auth()->user()->afip_information->iva_condition->name == 'Monotributista') {
            return $article->pivot->price;
        }
    }

    static function getIvaAmount($article) {
        if ($article->iva->percentage != 'No Gravado' || $article->iva->percentage != 'Exento') {
            return Numbers::redondear(Self::getPriceWithoutIva($article) * floatval($article->iva->percentage) / 100);
        } 
        return 0;
    }

    static function getImporteIva($article, $iva = null) {
        if (is_null($iva)) {
            return Self::getIvaAmount($article) * $article->pivot->amount;
        }
        if ($iva == '27' && $article->iva->percentage == '27') {
            return ['Importe' => Self::getIvaAmount($article) * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        if ($iva == '21' && $article->iva->percentage == '21') {
            return ['Importe' => Self::getIvaAmount($article) * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        if ($iva == '10.5' && $article->iva->percentage == '10.5') {
            return ['Importe' => Self::getIvaAmount($article) * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        if ($iva == '5' && $article->iva->percentage == '5') {
            return ['Importe' => Self::getIvaAmount($article) * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        if ($iva == '2.5' && $article->iva->percentage == '2.5') {
            return ['Importe' => Self::getIvaAmount($article) * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        if ($iva == '0' && $article->iva->percentage == '0') {
            return ['Importe' => Self::getIvaAmount($article) * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        if ($iva == 'Exento' && $article->iva->percentage == 'Exento') {
            return ['Importe' => $article->pivot->price * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        if ($iva == 'No Gravado' && $article->iva->percentage == 'No Gravado') {
            return ['Importe' => $article->pivot->price * $article->pivot->amount, 'BaseImp' => Self::getPriceWithoutIva($article) * $article->pivot->amount];
        }
        return ['Importe' => 0, 'BaseImp' => 0];
    }

    static function getImporteItem($article) {
        return $article->pivot->price * $article->pivot->amount;
    }

    static function getImporteGravado($article) {
        if ($article->iva->percentage != 'No Gravado' && $article->iva->percentage != 'Exento') {
            Log::info('Se suman '.Self::getPriceWithoutIva($article) * $article->pivot->amount.' al gravado del art '.$article->name);
            return Self::getPriceWithoutIva($article) * $article->pivot->amount;
        }
        return 0;
    }


    static function getImporteNeto($article) {
        return Self::getPriceWithoutIva($article) * $article->pivot->amount;
    }

}