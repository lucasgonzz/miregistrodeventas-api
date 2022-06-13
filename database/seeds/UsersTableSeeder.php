<?php

use App\AfipInformation;
use App\User;
use App\UserConfiguration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

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
            'id'                => 301,
            'name'              => 'Mi Negocio',
            'email'             => 'marcos@gmail.com',
            'company_name'      => 'Mi Negocio',
            'status'            => 'commerce',
            'plan_id'           => 1,
            'type'              => 'provider',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 20,
            'has_delivery'      => 1,
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'online_prices'     => 'only_registered',
            'order_description' => '¿Hay que envolver algo?',
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce = User::create([
            'id'                    => 302,
            'name'                  => 'Lucas',
            'email'                 => 'lucasgonzalez5500@gmail.com',
            'image_url'             => 'v1653571722/articles/ibqe6ejo529nslxsjlxv.jpg',
            'company_name'          => 'Fiushh',
            'phone'                 => '3444622138',
            'status'                => 'commerce',
            'plan_id'               => 3,
            'type'                  => 'commerce',
            'password'              => bcrypt('1234'),
            'percentage_card'       => 20,
            
            'has_delivery'          => 1,
            'delivery_price'        => 70,
            'online_prices'         => 'all',
            'online'                => 'http://kioscoverde.local:8080',
            'order_description'     => 'Observaciones',
            'dolar'                 => 'promedio',
            'dolar_cara_chica'      => 3,
            'dolar_plus'            => 3,
            'created_at'            => Carbon::now()->subMonths(2),
        ]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'A cta saldo',
            'current_acount_pagandose_details'      => 'A cta',
            'user_id'                               => $commerce->id,
        ]);
        AfipInformation::create([
            'iva_condition_id'      => 1,
            'razon_social'          => 'LUCAS GONZALEZ FIUSHH',
            'domicilio_comercial'   => 'Pellegrini 1876',
            'cuit'                  => '20175018841',
            'punto_venta'           => 4,
            'ingresos_brutos'       => '20175018841',
            'inicio_actividades'    => Carbon::now()->subYears(5),
            'user_id'               => $commerce->id,
        ]);

        $commerce = User::create([
            'id'                => 303,
            'name'              => 'Juliana',
            'email'             => 'juliana5500@gmail.com',
            'image_url'         => 'v1653571722/articles/ibqe6ejo529nslxsjlxv.jpg',
            'phone'             => '3444622139',
            'company_name'      => 'Pinocho',
            'status'            => 'commerce',
            'plan_id'           => 3,
            'type'              => 'commerce',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 20,
            'has_delivery'      => 1,
            'dolar'             => 'promedio',
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'dolar_plus'        => 3,
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce->extencions()->attach([5]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Me pago',
            'current_acount_pagandose_details'      => 'Me pago',
            'user_id'                               => $commerce->id,
        ]);
        AfipInformation::create([
            'iva_condition_id'      => 1,
            'razon_social'          => 'PINOCHO LIBREIRA',
            'domicilio_comercial'   => 'Pellegrini 1876',
            'cuit'                  => '20175018841',
            'punto_venta'           => 4,
            'ingresos_brutos'       => '20175018841',
            'inicio_actividades'    => Carbon::now()->subYears(5),
            'user_id'               => $commerce->id,
        ]);

        $commerce = User::create([
            'id'                => 304,
            'name'              => 'Bartolo',
            'email'             => 'lucasgonzalez210200@gmail.com',
            'image_url'         => 'v1653518851/articles/yiqq6hy84ww0gpk4ouwp.jpg',
            'phone'             => '3444622139',
            'company_name'      => 'KAS Aberturas',
            'status'            => 'commerce',
            'plan_id'           => 3,
            'type'              => 'provider',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 20,
            'has_delivery'      => 1,
            'dolar'             => 'promedio',
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'dolar_plus'        => 3,
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce->extencions()->attach([1, 2, 3]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Recibo de pago (saldado)',
            'current_acount_pagandose_details'      => 'Recibo de pago',
            'user_id'                               => $commerce->id,
        ]);

        AfipInformation::create([
            'iva_condition_id'      => 1,
            'razon_social'          => 'KAS ABERTURAS',
            'domicilio_comercial'   => 'Pellegrini 1876',
            'cuit'                  => '20175018841',
            'punto_venta'           => 4,
            'ingresos_brutos'       => '20175018841',
            'inicio_actividades'    => Carbon::now()->subYears(5),
            'user_id'               => $commerce->id,
        ]);
        
        $lucas = User::create([
            'id'            => 305,
            'name'          => 'Lucas',
        	'company_name'  => 'Lucas',
            'status'        => 'commerce',
            'plan_id'       => 3,
            'type'          => 'provider',
        	'password'      => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at'    => Carbon::now(),
            'expired_at'    => Carbon::now()->addWeek(),
        ]);

        $commerce = User::create([
            'id'              => 306,
            'name' => 'Fran',
            'company_name' => 'Lo de Fran',
            'status' => 'commerce',
            'plan_id'         => 2,
            'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addMonth(),
        ]);

        $super = User::create([
            'id'              => 307,
            'name' => 'Lucas super',
            'status' => 'super',
            'password' => bcrypt('1234'),
        ]);
    }
}
