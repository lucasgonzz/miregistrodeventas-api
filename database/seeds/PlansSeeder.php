<?php

use App\Feature;
use App\Permission;
use App\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lite = Plan::create([
            'name' => 'Lite',
            'preapproval_plan_id' => '2c9380847f8a011d017f8fb6594200f4',
            'price' => 1000,
            'modules' => 'Ingresar, Vender y Ventas.',
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->pluck('id');
        $lite->permissions()->sync($permissions);
        $lite->features()->sync($this->setFeatures([]));

        $basico = Plan::create([
            'name' => 'Basico',
            'preapproval_plan_id' => '2c9380847f8a00e7017f8fb8a26f00fc',
            'price' => 2000,
            'modules' => 'Ingresar, Listado, Vender, Ventas y Empleados.',
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'articles.index')
                                ->orWhere('slug', 'articles.cost')
                                ->orWhere('slug', 'articles.stock')
                                ->orWhere('slug', 'providers')
                                ->orWhere('slug', 'special_prices')
                                ->orWhere('slug', 'categories')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->orWhere('slug', 'employees')
                                ->orWhere('slug', 'discounts_sellers')
                                ->orWhere('slug', 'clients')
                                ->pluck('id');
        $basico->permissions()->sync($permissions);
        $features = Feature::where('text', 'Proveedores, Categorias, Precios especiales y Fotos en los productos.')
                            ->orWhere('text', 'Factura electronica.')
                            ->orWhere('text', 'Clientes y Cuentas corrientes.')
                            ->orWhere('text', 'Vendedores y Descuentos en las ventas.')
                            ->pluck('id');
        $basico->features()->sync($this->setFeatures($features));

        $premium = Plan::create([
            'name' => 'Premium',
            'preapproval_plan_id' => '2c9380847f8a011d017f8fbabc1200f5',
            'price' => 4000,
            'modules' => 'Ingresar, Listado, Vender, Ventas, Empleados y Online.',
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'articles.index')
                                ->orWhere('slug', 'articles.cost')
                                ->orWhere('slug', 'articles.stock')
                                ->orWhere('slug', 'articles.images')
                                ->orWhere('slug', 'providers')
                                ->orWhere('slug', 'special_prices')
                                ->orWhere('slug', 'categories')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->orWhere('slug', 'employees')
                                ->orWhere('slug', 'discounts_sellers')
                                ->orWhere('slug', 'clients')
                                ->orWhere('slug', 'online.orders')
                                ->orWhere('slug', 'online.questions')
                                ->orWhere('slug', 'online.buyers')
                                ->orWhere('slug', 'online.messages')
                                ->orWhere('slug', 'online.cupons')
                                ->orWhere('slug', 'online.calls')
                                ->orWhere('slug', 'tags')
                                ->orWhere('slug', 'colors')
                                ->orWhere('slug', 'sizes')
                                ->orWhere('slug', 'brands')
                                ->orWhere('slug', 'descriptions')
                                ->orWhere('slug', 'conditions')
                                ->orWhere('slug', 'articles.with_dolar')
                                ->pluck('id');
        $premium->permissions()->sync($permissions);
        $features = Feature::where('text', 'Proveedores, Categorias, Precios especiales y Fotos en los productos.')
                            ->orWhere('text', 'Factura electronica.')
                            ->orWhere('text', 'Clientes y Cuentas corrientes.')
                            ->orWhere('text', 'Vendedores y Descuentos en las ventas.')
                            ->orWhere('text', 'Tienda Online personalizada.')
                            ->pluck('id');
        $premium->features()->sync($this->setFeatures($features));
    }

    function setFeatures($features) {
        $all_features = Feature::all();
        $result = [];
        foreach ($all_features as $feature) {
            $is_active = false;
            foreach ($features as $feature_id) {
                if ($feature->id == $feature_id) {
                    $is_active = true;
                } 
            }
            $result[$feature->id] = ['active' => $is_active];
        }
        return $result;
    }
}
