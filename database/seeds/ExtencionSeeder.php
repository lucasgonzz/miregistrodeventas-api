<?php

use App\Extencion;
use Illuminate\Database\Seeder;

class ExtencionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $extencions = [
            [
                'name' => 'Presupuestos',
                'slug' => 'budgets',
            ],
            [
                'name' => 'Ordenes de Produccion',
                'slug' => 'order_productions',
            ],
            [
                'name' => 'Margen de ganancia en los artículos',
                'slug' => 'articles.percentage_gain',
            ],
            [
                'name' => 'Proveedores',
                'slug' => 'providers',
            ],
            [
                'name' => 'Combos',
                'slug' => 'combos',
            ],
            [
                'name' => 'Esconder ventas',
                'slug' => 'sales.hide',
            ],
        ];
        foreach ($extencions as $extencion) {
            Extencion::create([
                'name' => $extencion['name'],
                'slug' => $extencion['slug'],
            ]);
        }
    }
}
