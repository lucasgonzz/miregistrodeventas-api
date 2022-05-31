<?php

namespace App\Http\Controllers;

use App\AfipTicket;
use App\Afip\WSFE;
use App\Article;
use App\Http\Controllers\Helpers\AfipHelper;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Sale;
use App\WSMTXCA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AfipWsController extends Controller
{

    function __construct() {
        $this->testing = true;
        // Produccion
        // define ('WSDL_WSAA', public_path().'/afip/wsaa.wsdl'); 
        // define ('CERT', 'file://'.realpath(public_path().'/afip/comerciocity-alias_1775a2484a464aa3.crt')); 
        // define ('PRIVATEKEY', 'file://'.realpath(public_path().'/afip/privada.key')); 
        // define ("URL_WSAA", "https://wsaa.afip.gov.ar/ws/services/LoginCms");
        define ('TRA_xml', public_path().'/afip/wsaa/TRA.xml'); 
        define ('TRA_tmp', public_path().'/afip/wsaa/TRA.tmp'); 
        define ('TA_file', public_path().'/afip/wsaa/TA.xml'); 
        define ('CMS_file', public_path().'/afip/wsaa/CMS.txt'); 
        if ($this->testing) {
            $this->cert = 'file://'.realpath(public_path().'/afip/testing/MiCertificado.pem');
            $this->private_key = 'file://'.realpath(public_path().'/afip/testing/MiClavePrivada.key');
            $this->url_wsaa = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';
        } else {
            $this->cert = 'file://'.realpath(public_path().'/afip/comerciocity-alias_1775a2484a464aa3.crt');
            $this->private_key = 'file://'.realpath(public_path().'/afip/privada.key');
            $this->url_wsaa = 'https://wsaa.afip.gov.ar/ws/services/LoginCms';
        }
        // if ($this->testing) {
        //     $this->url_wsaa = 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx';
        // } else {
        //     $this->url_wsaa = 'https://servicios1.afip.gov.ar/wsfev1/service.asmx';
        // }


    }

    function init(Request $request, $sale_id) {
        $service = 'wsfe';
        // $service = 'wsmtxca';
        $this->checkWsaa($service);
        $this->wsfe($sale_id);
        // $this->wsmtxca($sale_id);
    }

    function checkWsaa($service) {
        if (file_exists(TA_file)) {
            $ta = new \SimpleXMLElement(file_get_contents(TA_file));
            if (!isset($ta->header->expirationTime) || !isset($ta->credentials->token) || !isset($ta->credentials->sign)) {
                Log::info('El TA no tiene los datos necesarios');
                $this->wsaa($service);
            } else if (strtotime($ta->header->expirationTime) < time()) {
                Log::info('El TA estaba vencido');
                $this->wsaa($service);
            }
        } else {
            Log::info('El TA no estaba creado');
            $this->wsaa($service);
        }
    }

    function wsaa($service) {
        $this->createTRA($service);
        $cms = $this->signTRA();
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
                                        $this->cert,
                                        $this->private_key,
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
        file_put_contents(CMS_file,$cms);
        // Log::info('cms:');
        // Log::info($cms);
        return $cms;
    }

    function callWSAA($cms) { 
        Log::info('callWSAA');
        $client = new \SoapClient($this->url_wsaa.'?WSDL', array(
            'location' => $this->url_wsaa,
            'trace' => 1,
            'exceptions' => 0
        ));
        $results = $client->loginCms(array('in0'=>$cms));
        file_put_contents(public_path()."/afip/wsaa/request.xml",$client->__getLastRequest());
        file_put_contents(public_path()."/afip/wsaa/response.xml",$client->__getLastResponse());
        if (is_soap_fault($results)) {
            Log::info("SOAP Fault: ".$results->faultcode."\n".$results->faultstring);
            exit("SOAP Fault: ".$results->faultcode."\n".$results->faultstring."\n");
        }
        return $results->loginCmsReturn;
    }

    function wsfe($sale_id) {
        $user = Auth()->user();
        $sale = Sale::where('id', $sale_id)->with('articles')->first();
        $punto_venta = $user->afip_information->punto_venta;
        $cuit_negocio = $user->afip_information->cuit;
        $cuit_cliente = $sale->client->cuit;
        $cbte_tipo = $this->getTipoCbte($sale->client);
        $wsfe = new WSFE(['testing'=> $this->testing, 'cuit_representada' => $cuit_negocio, 'for_wsfe' => true]);
        $wsfe->setXmlTa(file_get_contents(TA_file));
        $cbte_nro = AfipHelper::getNumeroComprobante($wsfe, $punto_venta, $cbte_tipo);
        Log::info('Numero comprobante: '.$cbte_nro);
        $importes = AfipHelper::getImportes($sale);
        $today = date('Ymd');
        $moneda_id = 'PES';
        $invoice = array(
            'FeCAEReq' => array(
                'FeCabReq' => array(
                    'CantReg'      => 1,
                    'CbteTipo'     => $cbte_tipo,                   
                    'PtoVta'       => $punto_venta,
                ),
                'FeDetReq' => array(
                    'FECAEDetRequest' => array(
                        'Concepto'     => 1,                
                        'DocTipo'      => AfipHelper::getDocType('Cuit'),           
                        'DocNro'       => $cuit_cliente,
                        'CbteDesde'    => $cbte_nro,
                        'CbteHasta'    => $cbte_nro,
                        'CbteFch'      => $today,
                        'ImpTotal'     => $importes['total'],
                        'ImpTotConc'   => $importes['neto_no_gravado'],
                        'ImpNeto'      => $importes['gravado'],
                        'ImpOpEx'      => $importes['exento'],
                        'ImpIVA'       => $importes['iva'],
                        'ImpTrib'      => 0,
                        'MonId'        => $moneda_id,
                        'MonCotiz'     => 1,
                    )
                )
            )
        );
        if (Auth()->user()->afip_information->iva_condition->name == 'Responsable inscripto') {
            $ivas = [];
            foreach ($importes['ivas'] as $iva) {
                $ivas[] = [
                    'Id'      => $iva['Id'],
                    'BaseImp' => $iva['BaseImp'],
                    'Importe' => $iva['Importe'],
                ];
            }
            $invoice['FeCAEReq']['FeDetReq']['FECAEDetRequest']['Iva'] = $ivas;
        }
        // Se visualiza el resultado con el CAE correspondiente al comprobante.
        $result = $wsfe->FECAESolicitar($invoice);
        print_r($result);
        $this->saveAfipTicket($result, $sale, $cbte_nro, $importes['total'], $moneda_id);
        $sale = SaleHelper::getFullModel($sale->id);
        return response()->json(['sale' => $sale], 201);
    }

    function saveAfipTicket($result, $sale, $cbte_nro, $importe_total, $moneda_id) {
        AfipTicket::create([
            'cuit_negocio'      => $result->FECAESolicitarResult->FeCabResp->Cuit,
            'iva_negocio'       => Auth()->user()->afip_information->iva_condition->name,
            'punto_venta'       => $result->FECAESolicitarResult->FeCabResp->PtoVta,
            'cbte_numero'       => $cbte_nro,
            'cbte_letra'        => $this->getTipoLetra($result->FECAESolicitarResult->FeCabResp->CbteTipo),
            'cbte_tipo'         => $result->FECAESolicitarResult->FeCabResp->CbteTipo,
            'importe_total'     => $importe_total,
            'moneda_id'         => $moneda_id,
            'resultado'         => $result->FECAESolicitarResult->FeCabResp->Resultado,
            'concepto'          => $result->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Concepto,
            'cuit_cliente'      => $result->FECAESolicitarResult->FeDetResp->FECAEDetResponse->DocNro,
            'iva_cliente'       => $sale->client->iva_condition->name,
            'cae'               => $result->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE,
            'cae_expired_at'    => $result->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto,
            'sale_id'           => $sale->id,
        ]);
    }

    function getTipoCbte($client) {
        if ($this->user()->afip_information->iva_condition->name == 'Responsable inscripto') {
            if ($client->iva_condition->name == 'Responsable inscripto') {
                return 1; #A
            } else if ($client->iva_condition->name == 'Monotributista') {
                return 1; #A
                // return 6; #B
            } else if ($client->iva_condition->name == 'Consumidor final') {
                return 6; #B
            } else if ($client->iva_condition->name == 'Exento') {
                return 6; #B
            }
        } else if ($this->user()->afip_information->iva_condition->name == 'Monotributista') {
            return 11; #C
        }
    }

    function getTipoLetra($cbte_tipo) {
        Log::info('getTipoLetra: '.$cbte_tipo);
        if ($cbte_tipo == 1) {
            return 'A';
        }
        if ($cbte_tipo == 6) {
            return 'B';
        }
        if ($cbte_tipo == 11) {
            return 'C';
        }
    }

    function wsmtxca($sale_id) {
        // $punto_venta = 4;
        $user = Auth()->user();
        $sale = Sale::where('id', $sale_id)->with('articles')->first();
        $punto_venta = $user->afip_information->punto_venta;
        // $cuit_negocio = '20423548984';
        // $cuit_negocio = '20175018841';
        $cuit_negocio = $user->afip_information->cuit;
        // $cuit_cliente = '20242112025';
        $cuit_cliente = $sale->client->cuit;
        $cbte_tipo = 1;
        $wsfe = new WSFE(['testing'=> false, 'cuit_representada' => $cuit_negocio]);
        $wsmtxca = new WSMTXCA(['testing'=> false, 'cuit_representada' => $cuit_negocio]);
        // $wsmtxca = new WSMTXCA(['testing'=> false, 'cuit_representada' => $this->user()->cuit]);
        $wsmtxca->setXmlTa(file_get_contents(TA_file));
        $pto_vta = [
            'consultaUltimoComprobanteAutorizadoRequest' => [
                'numeroPuntoVenta'    => $punto_venta,
                'codigoTipoComprobante'  => $cbte_tipo    
            ]
        ];
        $result = $wsmtxca->consultarUltimoComprobanteAutorizado($pto_vta);
        dd($result);
        $cbte_nro = $result->numeroComprobante + 1;
        Log::info('Numero comprobante: '.$cbte_nro);
        $importes = AfipHelper::getImportes($sale);
        $invoice = [
            'comprobanteCAERequest' => [
                'codigoTipoComprobante'     => 1,                    
                // 'codigoTipoComprobante'     => AfipHelper::getTipoComprobante($sale),                    
                'numeroPuntoVenta'          => $punto_venta,              
                // 'numeroPuntoVenta'          => $this->user()->punto_venta,              
                'numeroComprobante'         => 1,
                // 'numeroComprobante'         => $cbte_nro,
                'fechaEmision'              => date('c'),
                // 'codigoTipoAutorizacion'    => 'E',
                'codigoTipoDocumento'       => AfipHelper::getDocType('Cuit'),
                'numeroDocumento'           => $cuit_cliente,
                // 'numeroDocumento'           => $sale->client->cuit,
                'importeGravado'            => $importes['importe_gravado'],
                'importeNoGravado'          => 0,
                'importeExento'             => 0,
                'importeSubtotal'           => $importes['importe_subtotal'],
                // 'importeOtrosTributos'      => 0,
                'importeTotal'              => $importes['importe_total'], 
                'codigoMoneda'              => 'PES',
                'cotizacionMoneda'          => 1,
                'observaciones'             => '',
                'codigoConcepto'            => 1,  #Productos
                // 'arrayOtrosTributos' => [
                    // 'otroTributo' => [
                    //     'codigo' => 99,
                    //     'descripcion' => 'Otro Tributo',
                    //     'baseImponible' => 100.00,
                    //     'importe' => 1.00,
                    // ],
                // ],
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
                        'importe' => $importes['importe_iva'],
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

    // function wsfe($sale_id) {
    //     $cuit_negocio = '20175018841';
    //     $cuit_cliente = '20242112025';
    //     $cbte_tipo = 1;
    //     $punto_venta = 4;
    //     $wsfe = new WSFE(['testing' => false, 'cuit_representada' => $cuit_negocio, 'for_wsfe' => true]);
    //     $wsfe->setXmlTa(file_get_contents(TA_file));
    //     $cbte_nro = AfipHelper::getNumeroComprobante($wsfe, $punto_venta, $cbte_tipo);
    //     print_r('Numero de comprobante: '.$cbte_nro.'</br>');
    //     $today = date('Ymd');

    //     $sale = Sale::where('id', $sale_id)->with('articles')->first();
    //     $importes = AfipHelper::getImportes($sale);

    //     $invoice = array(
    //         'FeCAEReq' => array(
    //             'FeCabReq' => array(
    //                 'CantReg'      => 1,
    //                 'CbteTipo'     => $cbte_tipo,                   
    //                 'PtoVta'       => $punto_venta,
    //             ),
    //             'FeDetReq' => array(
    //                 'FECAEDetRequest' => array(
    //                     'Concepto'     => 1,                
    //                     'DocTipo'      => AfipHelper::getDocType('Cuit'),           
    //                     'DocNro'       => $cuit_cliente,
    //                     'CbteDesde'    => $cbte_nro,
    //                     'CbteHasta'    => $cbte_nro,
    //                     'CbteFch'      => $today,
    //                     'ImpTotal'     => $importes['importe_total'],
    //                     'ImpTotConc'   => 0,
    //                     'ImpNeto'      => $importes['importe_gravado'],
    //                     'ImpOpEx'      => 0,
    //                     'ImpIVA'       => $importes['importe_iva'],
    //                     'ImpTrib'      => 0,
    //                     // 'FchServDesde' => $today,
    //                     // 'FchServHasta' => $today,
    //                     // 'FchVtoPago'   => $today,
    //                     'MonId'        => 'PES',
    //                     'MonCotiz'     => 1,
    //                     'Iva'          => array(
    //                         'AlicIva' => array(
    //                             'Id'        => 5,
    //                             'BaseImp'   => $importes['importe_gravado'],
    //                             'Importe'   => $importes['importe_iva']
    //                         )
    //                     )
    //                 )
    //             )
    //         )
    //     );

    //     // Se visualiza el resultado con el CAE correspondiente al comprobante.
    //     $result = $wsfe->FECAESolicitar($invoice);
    //     dd($result);
    // }

    function getImportes($sale_id) {
        $sale = Sale::find($sale_id);
        $importes = AfipHelper::getImportes($sale);
        return response()->json(['importes' => $importes], 200);
    }

}
