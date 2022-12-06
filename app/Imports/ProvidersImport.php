<?php

namespace App\Imports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ImportHelper;
use App\Provider;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class ProvidersImport implements ToCollection
{

    public function __construct($columns, $start_row, $finish_row) {
        $this->columns = $columns;
        $this->start_row = $start_row;
        $this->finish_row = $finish_row;
        $this->ct = new Controller();
    }

    public function collection(Collection $rows) {
        $num_row = 1;
        if (is_null($this->finish_row) || $this->finish_row == '') {
            $this->finish_row = count($rows);
        } 
        foreach ($rows as $row) {
            if ($this->checkRow($row)) {
                if ($num_row >= $this->start_row && $num_row <= $this->finish_row) {
                    $codigo = ImportHelper::getColumnValue($row, 'codigo', $this->columns);
                    $nombre = ImportHelper::getColumnValue($row, 'nombre', $this->columns);
                    $provider = Provider::where('user_id', $this->ct->userId());
                    if (!is_null($codigo)) {
                        $provider = $provider->where('num', $codigo);
                    } else {
                        $provider = $provider->where('name', $nombre);
                    }
                    $provider = $provider->first();
                    $this->saveModel($row, $provider);
                } else if ($num_row > $this->finish_row) {
                    break;
                }
                $num_row++;
            }
        }
    }

    function checkRow($row) {
        Log::info($row);
        return !is_null(ImportHelper::getColumnValue($row, 'nombre', $this->columns));
    }

    function saveModel($row, $provider) {
        $localidad = ImportHelper::getColumnValue($row, 'localidad', $this->columns);
        $iva_condition = ImportHelper::getColumnValue($row, 'condicion_de_iva', $this->columns);
        ImportHelper::saveLocation($localidad, $this->ct);
        $data = [
            'name'              => ImportHelper::getColumnValue($row, 'nombre', $this->columns),
            'phone'             => ImportHelper::getColumnValue($row, 'telefono', $this->columns),
            'address'           => ImportHelper::getColumnValue($row, 'direccion', $this->columns),
            'location_id'       => $this->ct->getModelBy('locations', 'name', $localidad, true, 'id', true),
            'email'             => ImportHelper::getColumnValue($row, 'email', $this->columns),
            'iva_condition_id'  => $this->ct->getModelBy('iva_conditions', 'name', $iva_condition, false, 'id', true),
            'razon_social'      => ImportHelper::getColumnValue($row, 'razon_social', $this->columns),
            'cuit'              => ImportHelper::getColumnValue($row, 'cuit', $this->columns),
            'observations'      => ImportHelper::getColumnValue($row, 'observaciones', $this->columns),
        ];
        if (!is_null($provider)) {
            $provider->update($data);
        } else {
            $codigo = ImportHelper::getColumnValue($row, 'codigo', $this->columns);
            if (!is_null($codigo)) {
                $data['num'] = $codigo;
            } else {
                $data['num'] = $this->ct->num('providers');
            }
            $data['user_id'] = $this->ct->userId();
            Provider::create($data);
        }
    }


}
