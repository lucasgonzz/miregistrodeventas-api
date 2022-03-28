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
            'id'              => 301,
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
            'id'              => 302,
            'name'            => 'Lucas',
            'city'            => 'Rosario',
            'email'           => 'lucasgonzalez5500@gmail.com',
            'company_name'    => 'Fiushh',
            'status'          => 'commerce',
            'plan_id'         => 3,
            'type'            => 'commerce',
            'password'        => bcrypt('1234'),
            'percentage_card' => 20,
            'iva'             => 'Responsable inscripto',
            'has_delivery'    => 1,
            'delivery_price'  => 70,
            'online_prices'    => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'dolar_plus'        => 3,
            // 'admin_id'      => $admin->id,
            'created_at'      => Carbon::now()->subMonths(2),
            // 'expired_at'       => Carbon::now(),
        ]);
        
        $lucas = User::create([
            'id'              => 303,
            'name' => 'Lucas',
        	'company_name' => 'Lucas',
            'status' => 'commerce',
            'plan_id'         => 3,
            'type'     => 'provider',
        	'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addWeek(),
        ]);

        $commerce = User::create([
            'id'              => 304,
            'name' => 'Fran',
            'company_name' => 'Lo de Fran',
            'status' => 'commerce',
            'plan_id'         => 2,
            'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expired_at' => Carbon::now(),
        ]);

        $super = User::create([
            'id'              => 305,
            'name' => 'Lucas super',
            'status' => 'super',
            'password' => bcrypt('1234'),
        ]);
    }
}
