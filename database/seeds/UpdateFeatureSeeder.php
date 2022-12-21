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
                'name' => 'Excel',
                'description' => 'Luego de la importacion solo se seteara la fecha de actualizado a los articulos que se les haya hecho alguna modificacion.',
            ],
            [
                'name' => 'Dolar para proveedores',
                'description' => 'Ahora se puede asignar un valor de dolar para cada proveedor, para luego indicar en los articulos que su costo esta en dolares de ese proveedor.',
            ],
            [
                'name' => 'Cientes',
                'description' => 'Correccion en el guardado de datos.',
            ],
            [
                'name' => 'Imagenes de articulos',
                'description' => 'Correccion en el guardado y randerizado de imagenes.',
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
