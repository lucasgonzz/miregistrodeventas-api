<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name'          => 'Super Admin',
        ]);
        
        Role::create([
        	'name' => 'provider',
        ]);
        
        Role::create([
        	'name' => 'commerce',
        ]);
    }
}
