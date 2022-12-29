<?php

namespace App\Exports;

use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientExport implements FromCollection, WithHeadings, WithMapping
{

    public function map($client): array
    {
        return [
            $client->num,
            $client->name,
            $client->saldo,
            $client->phone,
            $client->email
            $client->address,
            GeneralHelper::getRelation($client, 'price_type'),
            GeneralHelper::getRelation($client, 'location'),
            $client->cuit,
            $client->razon_social,
            GeneralHelper::getRelation($client, 'iva_condition'),
            $client->description,
        ];
    }

    public function headings(): array
    {
        return [
            'Codigo',
            'Nombre',
            'Saldo',
            'Telefono',
            'Email',
            'Direccion',
            'Tipo de precio',
            'Localidad',
            'Cuit',
            'Razon social',
            'Condicion de iva',
            'Descripcion',
        ];
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $mdoels = Client::where('user_id', UserHelper::userId())
                        ->where('status', 'active')
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return $mdoels;
    }
}
