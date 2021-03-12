<?php

use Illuminate\Database\Seeder;
use App\SaleTime;
use App\User;
use Caffeinated\Shinobi\Models\Permission;

class SaleTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $marcos = User::where('company_name', 'Mi Negocio')->first();
        SaleTime::create([
    	'user_id' => $marcos->id,
    	'name' => 'Madrugada',
    	'from' => '20:00',
    	'to' => '04:00',
        ]);
        SaleTime::create([
    	'user_id' => $marcos->id,
    	'name' => 'Mañana',
    	'from' => '04:00',
    	'to' => '12:00',
        ]);
        SaleTime::create([
    	'user_id' => $marcos->id,
    	'name' => 'Tarde',
    	'from' => '12:00',
    	'to' => '16:00',
        ]);
        SaleTime::create([
    	'user_id' => $marcos->id,
    	'name' => 'Noche',
    	'from' => '16:00',
    	'to' => '20:00',
        ]);

        // Se crean los permisos para ver esos horarios

        Permission::create([
    		'user_id' => $marcos->id,
            'name' => 'Madrugada',
            'slug' => 'sale.index.madrugada',
            'description' => 'Podra ver todas las ventas de madrugada'
        ]);
        Permission::create([
    		'user_id' => $marcos->id,
            'name' => 'Mañana',
            'slug' => 'sale.index.manana',
            'description' => 'Podra ver todas las ventas de mañana'
        ]);
        Permission::create([
    		'user_id' => $marcos->id,
            'name' => 'Tarde',
            'slug' => 'sale.index.tarde',
            'description' => 'Podra ver todas las ventas de tarde'
        ]);
        Permission::create([
    		'user_id' => $marcos->id,
            'name' => 'Noche',
            'slug' => 'sale.index.noche',
            'description' => 'Podra ver todas las ventas de noche'
        ]);
    }
}
