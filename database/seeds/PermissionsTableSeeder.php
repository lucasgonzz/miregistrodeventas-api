<?php

use Illuminate\Database\Seeder;
use App\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Articles
        Permission::create([
            'name' => 'Ingresar articulos',
        	'slug' => 'articles.store',
        ]);
        Permission::create([
            'name' => 'Ver articulos',
            'slug' => 'articles.index',
        ]);
        Permission::create([
            'name' => 'Costos en los articulos',
            'slug' => 'articles.cost',
        ]);
        Permission::create([
            'name' => 'Stock en los articulos',
            'slug' => 'articles.stock',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ordenes de produccion
        |--------------------------------------------------------------------------
        */
        Permission::create([
            'name' => 'Ver Presupuestos',
            'slug' => 'production.budgets',
        ]);
        Permission::create([
            'name' => 'Ver Ordenes de Produccion',
            'slug' => 'production.order_productions',
        ]);

        
        // Fotos
        Permission::create([
            'name' => 'Fotos en los articulos',
            'slug' => 'articles.images',
        ]);
        
        // Proveedores
        Permission::create([
            'name' => 'Usar proveedores',
            'slug' => 'providers',
        ]);
        // Precios especiales
        Permission::create([
            'name' => 'Usar precios especiales',
            'slug' => 'special_prices',
        ]);
        // Categorias
        Permission::create([
            'name' => 'Usar categorias',
            'slug' => 'categories',
        ]);
        // Colores
        Permission::create([
            'name' => 'Usar colores',
            'slug' => 'colors',
        ]);
        // Talles
        Permission::create([
            'name' => 'Usar talles',
            'slug' => 'sizes',
        ]);
        // Marcas
        Permission::create([
            'name' => 'Usar marcas',
            'slug' => 'brands',
        ]);
        // Descripciones
        Permission::create([
            'name' => 'Usar descripciones',
            'slug' => 'descriptions',
        ]);
        // Condiciones
        Permission::create([
            'name' => 'Usar condiciones',
            'slug' => 'conditions',
        ]);
        // Tags
        Permission::create([
            'name' => 'Usar tags',
            'slug' => 'tags',
        ]);
        // Precios en dolares
        Permission::create([
            'name' => 'Usar precios en dolares',
            'slug' => 'articles.with_dolar',
        ]);

        // Clientes
        Permission::create([
            'name' => 'Ver clientes',
            'slug' => 'clients',
        ]);

        // Empleados
        Permission::create([
            'name' => 'Empleados',
            'slug' => 'employees',
        ]);


        // Ventas
        Permission::create([
            'name' => 'Vender',
            'slug' => 'sales.store',
        ]);
        Permission::create([
            'name' => 'Ver ventas',
            'slug' => 'sales.index',
        ]);
        Permission::create([
            'name' => 'Descuentos y vendedores',
            'slug' => 'discounts_sellers',
        ]);
        Permission::create([
            'name' => 'Factura electronica',
            'slug' => 'afip_tickets',
        ]);

        // Online
        Permission::create([
            'name' => 'Ver pedidos en la Tienda Online',
            'slug' => 'online.orders',
        ]);
        Permission::create([
            'name' => 'Ver preguntas en la Tienda Online',
            'slug' => 'online.questions',
        ]);
        Permission::create([
            'name' => 'Ver clientes en la Tienda Online',
            'slug' => 'online.buyers',
        ]);
        Permission::create([
            'name' => 'Ver mensajes en la Tienda Online',
            'slug' => 'online.messages',
        ]);
        Permission::create([
            'name' => 'Ver cupones en la Tienda Online',
            'slug' => 'online.cupons',
        ]);
        Permission::create([
            'name' => 'Recibir ordenes de llamada en la Tienda Online',
            'slug' => 'online.calls',
        ]);
        
    }
}
