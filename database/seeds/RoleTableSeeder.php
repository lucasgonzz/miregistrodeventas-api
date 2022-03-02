<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Role::create([
        //     'name' => 'Super Admin',
        // ]);
        
        // Role::create([
        // 	'name' => 'provider',
        // ]);
        
        // Role::create([
        // 	'name' => 'commerce',
        // ]);

        $lite = Role::create([
            'name' => 'Lite'
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->get();
        $lite->syncPermissions($permissions);

        $basico = Role::create([
            'name' => 'Basico'
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'articles.index')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->orWhere('slug', 'clients.index')
                                ->get();
        $basico->syncPermissions($permissions);

        $premium = Role::create([
            'name' => 'Premium'
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'articles.index')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->orWhere('slug', 'clients.index')
                                ->orWhere('slug', 'online.orders')
                                ->orWhere('slug', 'online.questions')
                                ->orWhere('slug', 'online.buyers')
                                ->orWhere('slug', 'online.messages')
                                ->orWhere('slug', 'online.cupons')
                                ->orWhere('slug', 'articles.index')
                                ->get();
        $premium->syncPermissions($permissions);
    }
}
