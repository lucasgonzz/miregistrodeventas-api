<?php

namespace App\Imports;

use App\Client;
use App\CurrentAcount;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ImportHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToCollection, WithHeadingRow {

    function __construct() {
        $this->ct = new Controller();
    }

    public function collection(Collection $rows) {
        foreach ($rows as $row) {
            if ($row['nombre'] != '') {
                $client = Client::where('user_id', $this->ct->userId());
                if (isset($row['codigo']) && $row['codigo'] != '') {
                    $client = $client->where('num', $row['codigo']);
                } else {
                    $client = $client->where('name', $row['nombre']);
                }
                $client = $client->first();
                $this->saveModel($row, $client);
            }
        }
    }

    function saveModel($row, $client) {
        ImportHelper::saveLocation($row, $this->ct);
        ImportHelper::savePriceType($row, $this->ct);
        $data = [
            'name'              => $row['nombre'],
            'phone'             => $row['telefono'],
            'address'           => $row['direccion'],
            'location_id'       => $this->ct->getModelBy('locations', 'name', $row['localidad'], true, 'id'),
            'email'             => $row['email'],
            'iva_condition_id'  => $this->ct->getModelBy('iva_conditions', 'name', $row['condicion_frente_al_iva'], false, 'id'),
            'razon_social'      => $row['razon_social'],
            'cuit'              => $row['cuit'],
            'description'       => $row['descripcion'],
            'price_type_id'     => $this->ct->getModelBy('price_types', 'name', $row['tipo_de_precio'], true, 'id'),
        ];
        if (!is_null($client)) {
            Log::info('actualizando cliente '.$client->name);
            $client->update($data);
        } else {
            if (isset($row['codigo']) && $row['codigo'] != '') {
                $data['num'] = $row['codigo'];
            } else {
                $data['num'] = $this->ct->num('clients');
            }
            $data['user_id'] = $this->ct->userId();
            $client = Client::create($data);
            Log::info('creando cliente '.$client->name);
        }
        if ($row['saldo_actual'] != '') {
            $current_acounts = CurrentAcount::where('client_id', $client->id)
                                            ->get();
            if (count($current_acounts) == 0) {
                $is_for_debe = false;
                $saldo_inicial = (float)$row['saldo_actual'];
                if ($saldo_inicial >= 0) {
                    $is_for_debe = true;
                }
                $current_acount = CurrentAcount::create([
                    'detalle'   => 'Saldo inicial',
                    'status'    => $is_for_debe ? 'sin_pagar' : 'pago_from_client',
                    'client_id' => $client->id,
                    'debe'      => $is_for_debe ? $saldo_inicial : null,
                    'haber'     => !$is_for_debe ? $saldo_inicial : null,
                    'saldo'     => $saldo_inicial,
                ]);
                Log::info('creando saldo inicial para '.$client->name);
            }
        }
    }
}
