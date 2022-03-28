<?php

namespace App\Imports;

use App\Client;
use App\Http\Controllers\Helpers\UserHelper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Client([
            'name'      => $row['nombre'],
            'surname'   => $row['apellido'],
            'address'   => $row['direccion'],
            'cuit'      => $row['cuit'],
            'iva_id'    => $row['iva'],
            'user_id'   => UserHelper::userId(),
        ]);
    }
}
