<?php

namespace App\Imports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ImportHelper;
use App\Provider;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProvidersImport implements ToCollection, WithHeadingRow
{
    
    public function __construct() {
        $this->ct = new Controller();
    }
    

    public function collection(Collection $rows) {
        foreach ($rows as $row) {
            $provider = Provider::where('user_id', $this->ct->userId());
            if (isset($row['codigo']) && $row['codigo'] != '') {
                $provider = $provider->where('num', $row['codigo']);
            }  else {
                $provider = $provider->where('name', $row['name']);
            }
            $provider = $provider->first();
            $this->saveModel($row, $provider);
        }
    }

    function saveModel($row, $provider) {
        ImportHelper::saveLocation($row, $this->ct);
        $data = [
            'name'              => $row['nombre'],
            'phone'             => $row['telefono'],
            'address'           => $row['direccion'],
            'location_id'       => $this->ct->getModelBy('locations', 'name', $row['localidad'], true, 'id', true),
            'email'             => $row['email'],
            'iva_condition_id'  => $this->ct->getModelBy('iva_conditions', 'name', $row['condicion_de_iva'], false, 'id', true),
            'razon_social'      => $row['razon_social'],
            'cuit'              => $row['cuit'],
            'observations'      => $row['observaciones'],
        ];
        if (!is_null($provider)) {
            $provider->update($data);
        } else {
            if (isset($row['codigo']) && $row['codigo'] != '') {
                $data['num'] = $row['codigo'];
            } else {
                $data['num'] = $this->ct->num('providers');
            }
            $data['user_id'] = $this->ct->userId();
            Provider::create($data);
        }
    }


}
