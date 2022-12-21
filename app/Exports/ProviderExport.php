<?php

namespace App\Exports;

use App\Http\Controllers\Helpers\UserHelper;
use App\Provider;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProviderExport implements FromCollection, WithHeadings, WithMapping
{

    public function map($provider): array
    {
        return [
            $provider->num,
            $provider->name,
            $provider->phone,
            $provider->direccion,
            !is_null($provider->location) ? $provider->location->name : null,
            $provider->email,
            !is_null($provider->iva_condition) ? $provider->iva_condition->name : null,
            $provider->razon_social,
            $provider->cuit,
            $provider->observations,
        ];
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $providers = Provider::where('user_id', UserHelper::userId())
                        ->where('status', 'active')
                        ->orderBy('created_at', 'DESC')
                        ->get();
        return $providers;
    }

    public function headings(): array
    {
        return [
            'Codigo',
            'Nombre',
            'Telefono',
            'Direccion',
            'Localidad',
            'Email',
            'Condicion de iva',
            'Razon social',
            'Cuit',
            'Observaciones',
        ];
    }
}
