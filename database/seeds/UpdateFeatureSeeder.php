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
                'name' => 'Edicion de pedidos de la tienda',
                'description' => 'Esta disponible la edicion de los articulos de los pedidos de la tienda online.',
            ],
            [
                'name' => 'Atajo a las recetas de los articulos',
                'description' => 'Ahora se puede ingresar a la receta de los articulos desde las ordenes de produccion desde el boton "receta".',
            ],
            [
                'name' => 'Informe mas preciso de la descarga de articulos',
                'description' => 'La aplicacion delegara toda la descarga de articulos a un segundo plano, e informara con mas precision el estado de la descarga de articulos.',
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
