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

        $this->fiushh();

        $this->pinocho();

        $this->candy();

        $this->kasAberturas();

        $this->nebulaStore();

        $this->mcElectronica();

        $this->colman();

        $this->super();

    }

    function fiushh() {
        $commerce = User::create([
            'id'                    => 302,
            'name'                  => 'Lucas',
            'email'                 => 'lucasgonzalez5500@gmail.com',
            'image_url'             => 'v1653571722/articles/ibqe6ejo529nslxsjlxv.jpg',
            'company_name'          => 'Fiushh',
            'phone'                 => '3444622138',
            'status'                => 'commerce',
            'plan_id'               => 6,
            'type'                  => 'commerce',
            'password'              => bcrypt('1234'),
            'percentage_card'       => 20,
            
            'has_delivery'          => 1,
            'delivery_price'        => 70,
            'online_prices'         => 'all',
            'online'                => 'http://kioscoverde.local:8080',
            'order_description'     => 'Observaciones',
            'dollar'                => 200,
            'created_at'            => Carbon::now()->subMonths(2),
        ]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'A cta saldo',
            'current_acount_pagandose_details'      => 'A cta',
            'iva_included'                          => 0,
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
    }

    function pinocho() {
        $commerce = User::create([
            'id'                => 303,
            'name'              => 'Juliana',
            'email'             => 'juliana5500@gmail.com',
            'image_url'         => 'v1655483046/articles/hxfzuliiuyt9b2gacmpo.png',
            'phone'             => '3444622139',
            'company_name'      => 'Pinocho',
            'status'            => 'commerce',
            'plan_id'           => 6,
            'type'              => 'commerce',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 20,
            'has_delivery'      => 1,
            'dollar'            => 200,
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce->extencions()->attach([3, 5]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Me pago',
            'current_acount_pagandose_details'      => 'Me pago',
            'show_articles_without_stock'           => false,
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
    }

    function candy() {
        $commerce = User::create([
            'id'                => 304,
            'name'              => 'Liliana',
            'email'             => 'juliana5500@gmail.com',
            'image_url'         => 'v1655302896/articles/tiao9oeep5fyfowxpbay.jpg',
            'phone'             => '3444622139',
            'company_name'      => 'CandyGuay',
            'status'            => 'commerce',
            'plan_id'           => 6,
            'type'              => 'commerce',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 20,
            'has_delivery'      => 1,
            'dollar'            => 200,
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
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
    }

    function kasAberturas() {
        $commerce = User::create([
            'id'                => 305,
            'name'              => 'Bartolo',
            'email'             => 'lucasgonzalez210200@gmail.com',
            'image_url'         => 'v1656680715/articles/crp5twb4jul34bcd98wr.jpg',
            'phone'             => '3444622139',
            'company_name'      => 'KAS Aberturas',
            'status'            => 'commerce',
            'plan_id'           => 6,
            'type'              => 'provider',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 0,
            'has_delivery'      => 1,
            'dollar'            => 200,
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce->extencions()->attach([1, 2, 3, 4, 7, 8]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Recibo de pago (saldado)',
            'current_acount_pagandose_details'      => 'Recibo de pago',
            'iva_included'                          => 0,
            'limit_items_in_sale_per_page'          => 3,
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
    }

    function nebulaStore() {
        $commerce = User::create([
            'id'                => 306,
            'name'              => 'Patricio',
            'email'             => 'lucasgonzalez5500@gmail.com',
            'image_url'         => 'v1653518851/articles/yiqq6hy84ww0gpk4ouwp.jpg',
            'phone'             => '3444622139',
            'company_name'      => 'NebulaStore',
            'status'            => 'commerce',
            'plan_id'           => 6,
            'type'              => 'commerce',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 0,
            'has_delivery'      => 1,
            'dollar'            => 200,
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce->extencions()->attach([3]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Recibo de pago (saldado)',
            'current_acount_pagandose_details'      => 'Recibo de pago',
            'iva_included'                          => 1,
            'user_id'                               => $commerce->id,
        ]);

        AfipInformation::create([
            'iva_condition_id'      => 1,
            'razon_social'          => '',
            'domicilio_comercial'   => '',
            'cuit'                  => '',
            'punto_venta'           => null,
            'ingresos_brutos'       => '',
            'inicio_actividades'    => null,
            'user_id'               => $commerce->id,
        ]);

    }

    function mcElectronica() {
        $commerce = User::create([
            'id'                => 307,
            'name'              => 'mc electronica',
            'email'             => 'lucasgonzalez210200@gmail.com',
            'image_url'         => 'v1656680715/articles/crp5twb4jul34bcd98wr.jpg',
            'phone'             => '3444622139',
            'company_name'      => 'mc electronica',
            'status'            => 'commerce',
            'plan_id'           => 6,
            'type'              => 'provider',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 0,
            'has_delivery'      => 1,
            'dollar'            => 200,
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce->extencions()->attach([1, 2, 3, 4, 7, 8]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Recibo de pago (saldado)',
            'current_acount_pagandose_details'      => 'Recibo de pago',
            'iva_included'                          => 0,
            'limit_items_in_sale_per_page'          => 3,
            'user_id'                               => $commerce->id,
        ]);

        AfipInformation::create([
            'iva_condition_id'      => 1,
            'razon_social'          => 'Colman',
            'domicilio_comercial'   => 'Pellegrini 1876',
            'cuit'                  => '20175018841',
            'punto_venta'           => 4,
            'ingresos_brutos'       => '20175018841',
            'inicio_actividades'    => Carbon::now()->subYears(5),
            'user_id'               => $commerce->id,
        ]);
    }

    function colman() {
        $commerce = User::create([
            'id'                => 308,
            'name'              => 'colman',
            'email'             => 'lucasgonzalez210200@gmail.com',
            'image_url'         => 'v1656680715/articles/crp5twb4jul34bcd98wr.jpg',
            'phone'             => '3444622139',
            'company_name'      => 'colman',
            'status'            => 'commerce',
            'plan_id'           => 6,
            'type'              => 'provider',
            'password'          => bcrypt('1234'),
            'percentage_card'   => 0,
            'has_delivery'      => 1,
            'dollar'            => 200,
            'delivery_price'    => 70,
            'online_prices'     => 'all',
            'online'            => 'http://kioscoverde.local:8080',
            'order_description' => 'Observaciones',
            'created_at'        => Carbon::now()->subMonths(2),
        ]);

        $commerce->extencions()->attach([1, 2, 3, 4, 7, 8]);
        UserConfiguration::create([
            'current_acount_pagado_details'         => 'Recibo de pago (saldado)',
            'current_acount_pagandose_details'      => 'Recibo de pago',
            'iva_included'                          => 0,
            'limit_items_in_sale_per_page'          => null,
            'user_id'                               => $commerce->id,
        ]);

        AfipInformation::create([
            'iva_condition_id'      => 1,
            'razon_social'          => 'Colman',
            'domicilio_comercial'   => 'Pellegrini 1876',
            'cuit'                  => '20175018841',
            'punto_venta'           => 4,
            'ingresos_brutos'       => '20175018841',
            'inicio_actividades'    => Carbon::now()->subYears(5),
            'user_id'               => $commerce->id,
        ]);
    }

    function super() {
        $super = User::create([
            'id'              => 309,
            'name' => 'Lucas super',
            'status' => 'super',
            'password' => bcrypt('1234'),
        ]);
    }
}
