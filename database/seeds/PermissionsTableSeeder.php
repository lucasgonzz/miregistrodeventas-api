<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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
        ]);
        Permission::create([
            'name' => 'Ver articulos',
        ]);
        Permission::create([
            'name' => 'Ver costos de articulos',
        ]);

        // Clientes
        Permission::create([
            'name' => 'Ver clientes',
        ]);

        // Ventas
        Permission::create([
            'name' => 'Vender',
        ]);
        Permission::create([
            'name' => 'Ver ventas',
        ]);

        // Online
        Permission::create([
            'name' => 'Online',
        ]);
        
    }
}
