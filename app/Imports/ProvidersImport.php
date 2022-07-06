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
            $this->saveModel($row);
        }
    }

    function saveModel($row) {
        ImportHelper::saveLocation($row, $this->ct);
        $model = Provider::create([
            'num'               => $this->ct->num('providers'),
            'name'              => $row['nombre'],
            'phone'             => $row['telefono'],
            'address'           => $row['direccion'],
            'location_id'       => $this->ct->getModelBy('locations', 'name', $row['localidad'], true, 'id', true),
            'email'             => $row['email'],
            'iva_condition_id'  => $this->ct->getModelBy('iva_conditions', 'name', $row['condicion_de_iva'], false, 'id', true),
            'razon_social'      => $row['razon_social'],
            'cuit'              => $row['cuit'],
            'observations'      => $row['observaciones'],
            'user_id'           => $this->ct->userId(),
        ]);
    }


}
