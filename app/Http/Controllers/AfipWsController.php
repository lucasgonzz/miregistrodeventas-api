<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Controllers\Helpers\AfipHelper;
use App\Sale;
use App\WSMTXCA;
use Illuminate\Http\Request;
use phpWsAfip\WS\WSAA;
use phpWsAfip\WS\WSN;

class AfipWsController extends Controller
{

    function __construct() {
        # WSDL correspondiente al WSAA
        define ('WSDL_WSAA', public_path().'/afip/wsaa.wsdl'); 
        # Certificado usado para firmar
        // define ('CERT', 'file://'.realpath(public_path().'/afip/MiCertificado.pem') ); 
        define ('CERT', 'file://'.realpath(public_path().'/afip/comerciocity-alias_1775a2484a464aa3.crt')); 
        # Clave privada del certificado
        // define ('PRIVATEKEY', 'file://'.realpath(public_path().'/afip/MiClavePrivada.key')); 
        define ('PRIVATEKEY', 'file://'.realpath(public_path().'/afip/privada.key')); 
        define ('TRA_xml', public_path().'/afip/TRA.xml'); 
        define ('TRA_tmp', public_path().'/afip/TRA.tmp'); 
        define ('TA_file', public_path().'/afip/ta.xml'); 
        // define ("URL_WSAA", "https://wsaahomo.afip.gov.ar/ws/services/LoginCms");
        define ("URL_WSAA", "https://wsaa.afip.gov.ar/ws/services/LoginCms");
    }

    function init($sale_id) {
        $service = 'wsmtxca';
        if (file_exists(TA_file)) {
            $ta = new \SimpleXMLElement(file_get_contents(TA_file));
            if (!isset($ta->header->expirationTime) || !isset($ta->credentials->token) || !isset($ta->credentials->sign) || strtotime($ta->header->expirationTime) < time()) {
                print_r('El TA esta vencido, se va a crear de nuevo </br>');
                $this->wsaa('wsmtxca');
            }
        } else {
            print_r('El TA no esta creado, se va a crear uno nuevo </br>');
            $this->wsaa('wsmtxca');
        }
        $this->wsmtxca($sale_id);
    }

    function wsaa($service) {
        $this->createTRA($service);
        $cms = $this->signTRA();
        // print_r($cms."</br>");
        $ta = $this->callWSAA($cms);
        file_put_contents(TA_file, $ta);
    }

    function createTRA($service) {
        $tra = new \SimpleXMLElement(
                    '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<loginTicketRequest version="1.0">'.
                    '</loginTicketRequest>');
        $tra->addChild('header');
        $tra->header->addChild('uniqueId',date('U'));
        $tra->header->addChild('generationTime',date('c',date('U')-60));
        $tra->header->addChild('expirationTime',date('c',date('U')+60));
        $tra->addChild('service', $service);
        $tra->asXML(TRA_xml);
    }

    function signTRA() {
        $status = openssl_pkcs7_sign(
                                        TRA_xml, 
                                        TRA_tmp, 
                                        CERT,
                                        PRIVATEKEY,
                                        array(),
                                        !PKCS7_DETACHED
                                    );
        if (!$status) { 
            exit("ERROR generating PKCS#7 signature\n"); 
        }
        $inf = fopen(TRA_tmp, "r");
        $i = 0;
        $cms = "";
        while (!feof($inf)) {
            $buffer = fgets($inf);
            if ( $i++ >= 4 ) {$cms.=$buffer;}
        }
        fclose($inf);
        unlink(TRA_tmp);
        return $cms;
    }

    function callWSAA($cms) { 
        $client = new \SoapClient(WSDL_WSAA, array(
            'location' => URL_WSAA,
            'trace' => 1,
            'exceptions' => 0
        ));
        $results = $client->loginCms(array('in0'=>$cms));
        file_put_contents(public_path()."/afip/request-loginCms.xml",$client->__getLastRequest());
        file_put_contents(public_path()."/afip/response-loginCms.xml",$client->__getLastResponse());
        if (is_soap_fault($results)) {
            exit("SOAP Fault: ".$results->faultcode."\n".$results->faultstring."\n");
        }
        return $results->loginCmsReturn;
    }

    function wsmtxca($sale_id) {
        $punto_venta = 4;
        $cuit_negocio = '20175018841';
        $cuit_cliente = '20242112025';
        $cbte_tipo = 1;
        $wsmtxca = new WSMTXCA(['testing'=> false, 'cuit_representada' => $cuit_negocio]);
        // $wsmtxca = new WSMTXCA(['testing'=> false, 'cuit_representada' => $this->user()->cuit]);
        $wsmtxca->setXmlTa(file_get_contents(TA_file));
        // $pto_vta = [
        //     'consultaUltimoComprobanteAutorizadoRequest' => [
        //         'numeroPuntoVenta'    => $punto_venta,
        //         'codigoTipoComprobante'  => $cbte_tipo    
        //     ]
        // ];
        // $result = $wsmtxca->consultarUltimoComprobanteAutorizado($pto_vta);
        // $cbte_nro = $result->numeroComprobante + 1;
        // print_r('Numero comprobante: '.$cbte_nro.'</br>');
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

        $items = [];
        $sale = Sale::where('id', $sale_id)->with('articles')->first();
        $importe_gravado = 0;
        $importe_iva = 0;
        $importe_total = 0;
        foreach ($sale->articles as $article) {
            $item = [
                'codigo' => !is_null($article->bar_code) ? $article->bar_code : substr($article->name, 0,3),
                'descripcion'           => $article->name,
                'cantidad'              => $article->pivot->amount,
                'codigoUnidadMedida'    => 7,
                'precioUnitario'        => $article->pivot->price,
                'importeBonificacion'   => 0,
                'codigoCondicionIVA'    => 5,
                'importeItem'           => AfipHelper::getImporteItem($article),
                'importeIVA'            => AfipHelper::getImporteIva($article),
            ];
            // Checke que sea factura A para discriminar el IVA
            // if (AfipHelper::getTipoComprobante($sale) == 1) {
            //     $item['importeIVA'] = AfipHelper::getImporteIva($article);
            // }
            $importe_gravado    += AfipHelper::getImporteGravado($article);
            $importe_iva        += AfipHelper::getImporteIva($article);
            $importe_total      += AfipHelper::getImporteItem($article);
        }

        $invoice = [
            'comprobanteCAERequest' => [
                'codigoTipoComprobante'     => 1,                    
                // 'codigoTipoComprobante'     => AfipHelper::getTipoComprobante($sale),                    
                'numeroPuntoVenta'          => $punto_venta,              
                // 'numeroPuntoVenta'          => $this->user()->punto_venta,              
                'numeroComprobante'         => 1,
                // 'numeroComprobante'         => $cbte_nro,
                'fechaEmision'              => date('c'),
                'codigoTipoAutorizacion'    => 'E',
                'codigoTipoDocumento'       => $doc_type['Cuit'],
                'numeroDocumento'           => $cuit_cliente,
                // 'numeroDocumento'           => $sale->client->cuit,
                'importeGravado'            => $importe_gravado,
                'importeNoGravado'          => 0,
                'importeExento'             => 0,
                'importeSubtotal'           => $importe_total,
                'importeOtrosTributos'      => 0,
                'importeTotal'              => $importe_total, 
                'codigoMoneda'              => 'PES',
                'cotizacionMoneda'          => 1,
                'observaciones'             => '',
                'codigoConcepto'            => 1,  #Productos
                'arrayOtrosTributos' => [
                    // 'otroTributo' => [
                    //     'codigo' => 99,
                    //     'descripcion' => 'Otro Tributo',
                    //     'baseImponible' => 100.00,
                    //     'importe' => 1.00,
                    // ],
                ],
                // 'arrayItems' => [
                //     'item' => [
                //         'unidadesMtx' => 123456,
                //         'codigoMtx' => '0123456789913',
                //         'codigo' => 'P0001',
                //         'descripcion' => 'Descripción del producto P0001',
                //         'cantidad' => 1.00,
                //         'codigoUnidadMedida' => 7,
                //         'precioUnitario' => 100.00,
                //         'importeBonificacion' => 0,
                //         'codigoCondicionIVA' => 5,
                //         'importeIVA' => 21.00,
                //         'importeItem' => 121.00,
                //     ],
                // ],
                'arraySubtotalesIVA' => [
                    'subtotalIVA' => [
                        'codigo' => 5,
                        'importe' => $importe_iva,
                    ],
                ],
            ],
        ];
        $invoice['comprobanteCAERequest']['arrayItems'] = $items;
        // dd($invoice);

        // Se visualiza el resultado con el CAE correspondiente al comprobante.
        $result = $wsmtxca->autorizarComprobante($invoice);
        file_put_contents(public_path().'/afip/result.xml', $result);
        dd($result);
    }

}
