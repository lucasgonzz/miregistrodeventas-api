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
            'status'          => 'commerce',
            'plan_id'         => 1,
            'type'            => 'provider',
            'password'        => bcrypt('1234'),
            'percentage_card' => 20,
            'iva'             => 'Responsable inscripto',
            'has_delivery'    => 1,
            'delivery_price'  => 70,
            'online_prices'   => 'all',
            'online'          => 'http://kioscoverde.local:8080',
            'online_prices'   => 'only_registered',
            'order_description' => '¿Hay que envolver algo?',
            // 'admin_id'      => $admin->id,
            'created_at'      => Carbon::now()->subMonths(2),
        ]);

        $commerce = User::create([
            'name'            => 'Lucas',
            'city'            => 'Rosario',
            'email'           => 'lucasgonzalez5500@gmail.com',
            'company_name'    => 'Fiushh',
            'status'          => 'commerce',
            'plan_id'         => 2,
            'type'            => 'commerce',
            'password'        => bcrypt('1234'),
            'percentage_card' => 20,
            'iva'             => 'Responsable inscripto',
            'has_delivery'    => 1,
            'delivery_price'  => 70,
            'online_prices'    => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'with_dolar' => 0,
            // 'admin_id'      => $admin->id,
            'created_at'      => Carbon::now()->subMonths(2),
            'expire_at'       => Carbon::now()->addDays(3),
        ]);
        
        $lucas = User::create([
            'name' => 'Lucas',
        	'company_name' => 'Lucas',
            'status' => 'commerce',
            'plan_id'         => 3,
            'type'     => 'provider',
        	'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expire_at' => Carbon::now()->addWeek(),
        ]);

        $commerce = User::create([
            'name' => 'Fran',
            'company_name' => 'Lo de Fran',
            'status' => 'commerce',
            'plan_id'         => 2,
            'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expire_at' => Carbon::now(),
        ]);

        $super = User::create([
            'name' => 'Lucas super',
            'status' => 'super',
            'password' => bcrypt('1234'),
        ]);
    }
}
