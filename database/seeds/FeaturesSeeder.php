<?php

use App\Feature;
use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $features = [
            'Proveedores, Categorias, Precios especiales y Fotos en los productos.',
            'Factura electronica.',
            'Clientes y Cuentas corrientes.',
            'Vendedores y Descuentos en las ventas.',
            'Tienda Online personalizada.',
        ];
        foreach ($features as $feature) {
            Feature::create([
                'text' => $feature
            ]);
        }
    }
}
