<?php

use Illuminate\Database\Seeder;
use Caffeinated\Shinobi\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	// Ventas

        // Articles
        Permission::create([
        	'name' => 'Ingresar artículos',
        	'slug' => 'article.create',
        	'description' => 'Podrá registrar cualquier cantidad de artículos'
        ]);
        Permission::create([
            'name' => 'Ver los artículos',
            'slug' => 'article.index',
            'description' => 'Podrá ver todos los artículos que hayan sido ingresados'
        ]);
        Permission::create([
            'name' => 'Ver los costos de los artículos',
            'slug' => 'article.index.cost',
            'description' => 'Podrá ver todos los costos de todos los artículos que hayan sido ingresados'
        ]);

        // Clientes
        Permission::create([
            'name' => 'Ver los clientes y sus deudas',
            'slug' => 'client',
            'description' => 'Podrá ver y buscar entre los clientes registrados en el sistema, cambiar sus nombres y sus deudas',
        ]);

        // Ventas
        Permission::create([
            'name' => 'Vender',
            'slug' => 'sale.create',
            'description' => 'Podrá vender cualquier artículo registrado en el sistema'
        ]);
        Permission::create([
            'name' => 'Ver las ventas',
            'slug' => 'sale.index',
            'description' => 'Podrá ver las ventas registradas en el sistema'
        ]);
        Permission::create([
            'name' => 'Ver las ventas de cualquier horario',
            'slug' => 'sale.index.all',
            'description' => 'Podrá ver todas las ventas registradas en el sistema de cualquier horario'
        ]);
        Permission::create([
            'name' => 'Ver solo las ventas del día',
            'slug' => 'sale.index.only_day',
            'description' => 'Podrá ver solo las ventas hechas durante el día.'
        ]); 

        // Permisos para acciones
        Permission::create([
            'name' => 'Vender online',
            'slug' => 'online',
            // 'user_id' => 0,
            'description' => 'Podrá vender online.',
            'price' => 100,
        ]);
        Permission::create([
            'name' => 'Aplicar recargo a ventas con tarjeta',
            'slug' => 'percentage_card',
            // 'user_id' => 0,
            'description' => 'Podrá aplicar un recargo a las ventas que sean con tarjeta y cambiar ese recargo cuando sea oportuno',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Usar marcadores',
            'slug' => 'marker',
            // 'user_id' => 0,
            'description' => 'Podrá crear marcadores y grupo de marcadores para tener a mano los artículos que considere en la pagina de vender',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Editar las ventas',
            'slug' => 'sale.update',
            // 'user_id' => 0,
            'description' => 'Podrá retroceder a las ventas anteriores desde la pagina de vender y editar las ventas agregando o quitando artículos',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Vender a clientes',
            'slug' => 'client',
            // 'user_id' => 0,
            'description' => 'Podrá registrar clientes y asignarcelos a las ventas, indicando también si paga el total de la venta o queda debiendo y posteriormente saldar sus deudas, también podrá ver las ventas a un cliente en especifico',
            'price' => 50,
        ]);
        Permission::create([
            'name' => 'Vender articulos incotables',
            'slug' => 'article.uncontable',
            // 'user_id' => 0,
            'description' => 'Podrá registrar artículos incontables, para guardar sus precios por kilos o gramos y venderlos en las cantidades que quiera',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Imprimir tickets',
            'slug' => 'tickets',
            // 'user_id' => 0,
            'description' => 'Podrá imprimir los tickets de los artículos que este ingresando o de cualquiera que tenga registrado en el sistema',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Hacer ofertas',
            'slug' => 'article.offer_price',
            // 'user_id' => 0,
            'description' => 'Podrá hacer ponerle precio de oferta a sus artículos que saldrán resaltados en los tickets',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Horarios de ventas',
            'slug' => 'sale_time',
            // 'user_id' => 0,
            'description' => 'Podrá crear horarios de ventas para tener mas organizados sus registros',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Emprimir ventas',
            'slug' => 'sale.print',
            // 'user_id' => 0,
            'description' => 'Podrá imprimir sus ventas mostrando solo los datos que quiera',
            'price' => 25,
        ]);
        Permission::create([
            'name' => 'Empleados',
            'slug' => 'employee',
            // 'user_id' => 0,
            'description' => 'Podrá registrar empleados para asignarle permisos dentro del sistema, como ver las ventas de ciertos horarios, ver los costos de la mercadería, etc',
            'price' => 50,
        ]);
        
    }
}
