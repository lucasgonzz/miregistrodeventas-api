<?php

namespace App\Imports;

use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ImportHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToCollection, WithHeadingRow {

    function __construct() {
        $this->ct = new Controller();
    }

    public function collection(Collection $rows) {
        foreach ($rows as $row) {
            $this->saveModel($row);
        }
    }

    function saveModel($row) {
        ImportHelper::saveLocation($row, $this->ct);
        $model = Client::create([
            'num'               => $this->ct->num('clients'),
            'name'              => $row['nombre'],
            'phone'             => $row['telefono'],
            'address'           => $row['direccion'],
            'location_id'       => $this->ct->getModelBy('locations', 'name', $row['localidad'], true, 'id'),
            'email'             => $row['email'],
            'iva_condition_id'  => $this->ct->getModelBy('iva_conditions', 'name', $row['condicion_de_iva'], false, 'id'),
            'razon_social'      => $row['razon_social'],
            'cuit'              => $row['cuit'],
            'user_id'           => $this->ct->userId(),
        ]);
    }
}
