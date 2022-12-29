<?php

use App\UpdateFeature;
use Illuminate\Database\Seeder;

class UpdateFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $models = [
            [
                'name' => 'Excel para CLIENTES',
                'description' => 'Esta disponible la opcion para exportar e importar archivo excel de tus clientes.',
            ],
            [
                'name' => 'Imprimir CLIENTES',
                'description' => 'Esta disponible la opcion para generar un PDF con tus clientes.',
            ],
            [
                'name'  => 'Exportar solo los FILTRADOS',
                'description' => 'Luego de hacer una busqueda, vas a poder exportar a Excel o PDF solo los resultados filtrados.'
            ],
            [
                'name'  => 'Iva en los pedidos a proveedores',
                'description' => 'Opcion para especificar si el Iva esta incluido en el precio final y en cuenta corriente en los pedidos a proveedores.'
            ],
            [
                'name'  => 'Actualizar contraseña a empleados',
                'description' => 'Opcion para cambiar la contraseña a los empleados.'
            ],
            [
                'name'  => 'Actualizacion en los saldos de los clientes',
                'description' => 'Despues de hacer o actualizar una venta, se actualizara automaticamente el saldo del cliente.'
            ],
        ];
        foreach ($models as $model) {
            UpdateFeature::create([
                'name'          => $model['name'],
                'description'   => $model['description'],
            ]);
        }
    }
}
