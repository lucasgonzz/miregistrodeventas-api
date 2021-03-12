<?php

use Illuminate\Database\Seeder;
use Caffeinated\Shinobi\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Role::create([
        //     'name'          => 'Admin',
        //     'slug'          => 'admin',
        //     'description'   => 'Aceso total a todas las funciones del sistema',
        //     'special'       => 'all-access'
        // ]);
        // Role::create([
        //     'name'          => 'Owner',
        //     'slug'          => 'owner',
        //     'description'   => 'Aceso total a todas las funciones del sistema',
        // ]);
        Role::create([
            'name'          => 'Administrador',
            'slug'          => 'admin',
            'description'   => 'Aceso total a todas las funciones del sistema',
            'special'       => 'all-access'
        ]);
        
        Role::create([
        	'name' => 'provider',
        	'slug' => 'provider',
        	'description' => 'Vende al por mayor',
        ]);
        
        Role::create([
        	'name' => 'commerce',
        	'slug' => 'commerce',
        	'description' => 'Vende al por menor',
        ]);
    }
}
