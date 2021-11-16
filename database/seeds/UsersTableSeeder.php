<?php

use Illuminate\Database\Seeder;
use App\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $commerce = User::create([
            'name'            => 'Mi Negocio',
            'city'            => 'Gualeguay',
            'email'           => 'marcos@gmail.com',
            'company_name'    => 'Mi Negocio',
            'status'          => 'trial',
            'password'        => bcrypt('1234'),
            'percentage_card' => 20,
            'has_delivery'    => 1,
            'delivery_price'  => 70,
            'online_prices'   => 'all',
            'online'          => 'http://kioscoverde.local:8080',
            'online_prices'   => 'only_registered',
            'order_description' => '¿Hay que envolver algo?',
            // 'admin_id'      => $admin->id,
            'created_at'      => Carbon::now()->subMonths(2),
            'expire'          => Carbon::now()->subDay(),
        ]);
        $commerce->roles()->sync([1,3]);

        $commerce = User::create([
            'name'            => 'Lucas',
            'city'            => 'Rosario',
            'email'           => 'lucasgonzalez5500@gmail.com',
            'company_name'    => 'Fiushh',
            'status'          => 'trial',
            'password'        => bcrypt('1234'),
            'percentage_card' => 20,
            'has_delivery'    => 1,
            'delivery_price'  => 70,
            'online_prices'    => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'with_dolar' => 0,
            // 'admin_id'      => $admin->id,
            'created_at'      => Carbon::now()->subMonths(2),
            'expire'          => Carbon::now()->subDay(),
        ]);
        $commerce->roles()->sync([1,3]);
        
        $lucas = User::create([
            'name' => 'Lucas',
        	'company_name' => 'Lucas',
            'status' => 'in_use',
        	'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expire' => Carbon::now()->addWeeks(4),
        ]);
        $lucas->roles()->sync([1,2]);

        $commerce = User::create([
            'name' => 'Fran',
            'company_name' => 'Lo de Fran',
            'status' => 'trial',
            'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expire' => Carbon::now(),
        ]);
        $commerce->roles()->sync([1,3]);
        // $commerce->permissions()->sync([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // $commerce = User::create([
        //     'name' => 'Juan',
        //     'company_name' => 'Lo de Juan',
        //     'status' => 'trial',
        //     'password' => bcrypt('1234'),
        //     'admin_id' => $admin->id,
        //     'created_at' => Carbon::now()->subWeeks(2),
        //     'expire' => Carbon::now()->subDay(),
        // ]);
        // $commerce->roles()->sync([1,3]);
        // $commerce->permissions()->sync([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // for ($user_id=1; $user_id <= 8; $user_id++) { 
        //     $commerce = User::create([
        //         'company_name' => 'Negocio '.$user_id,
        //         'status' => 'for_trial',
        //         'password' => bcrypt('1234'),
        //         'admin_id' => $admin->id,
        //         'created_at' => Carbon::now(),
        //     ]);
        //     $commerce->roles()->sync([1,3]);
        //     $commerce->permissions()->sync([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);
        // }

        // $super = User::create([
        //     'name' => 'Lucas',
        //     'status' => 'super',
        //     'password' => bcrypt('1234'),
        // ]);

        // $admin = User::create([
        //     'name' => 'Admin',
        //     'status' => 'admin',
        //     'password' => bcrypt('1234'),
        // ]);
    }
}
