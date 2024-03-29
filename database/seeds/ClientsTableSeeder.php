<?php

use App\Client;
use App\User;
use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->mcElectronica();
        $this->colman();
        $provider = User::where('company_name', 'kas aberturas')->first();
        $users = User::where('company_name', 'kas aberturas')
                        ->orWhere('company_name', 'pinocho')
                        ->orWhere('company_name', 'candyguay')
                        ->get();
        foreach ($users as $user) {
            Client::create([
                'name'              => 'Marcos Gonzalez',
                'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                'cuit'              => '20242112025',
                'razon_social'      => 'MARCOS SRL', 
                'iva_condition_id'  => 1,
                'user_id'           => $user->id,
                'seller_id'         => $user->id == $provider->id ? 1 : null
            ]);
            Client::create([
                'name'              => 'Lucas Gonzalez',
                'email'             => 'lucasgonzalez5500@gmail.com',
                'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                'cuit'              => '20242112025',
                'razon_social'      => 'MARCOS SRL', 
                'iva_condition_id'  => 1,
                'user_id' => $user->id,
                'seller_id' => $user->id == $provider->id ? 1 : null
            ]);
            Client::create([
                'name'              => 'Luquis Gonzalez',
                'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                'cuit'              => '20242112025',
                'razon_social'      => 'MARCOS SRL', 
                'iva_condition_id'  => 1,
                'user_id' => $user->id,
                'seller_id' => $user->id == $provider->id ? 2 : null
            ]);
            Client::create([
                'name'              => 'Marta Gonzalez',
                'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                'cuit'              => '20242112025',
                'razon_social'      => 'MARCOS SRL', 
                'iva_condition_id'  => 1,
                'user_id' => $user->id,
                'seller_id' => $user->id == $provider->id ? 2 : null
            ]);
            Client::create([
                'name'              => 'Juana Gonzalez',
                'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                'cuit'              => '20242112025',
                'razon_social'      => 'MARCOS SRL', 
                'iva_condition_id'  => 1,
                'user_id' => $user->id,
                'seller_id' => $user->id == $provider->id ? 2 : null
            ]);
        }
    }

    function mcElectronica() {
        $mc_electronica = User::where('company_name', 'mc electronica')->first();

        $colman = User::where('company_name', 'colman')->first();
        Client::create([
            'name'                  => 'Colman',
            'address'               => 'San antonio 23 - Gualeguay, Entre Rios',
            'cuit'                  => '20242112025',
            'razon_social'          => 'MARCOS SRL', 
            'iva_condition_id'      => 1,
            'user_id'               => $mc_electronica->id,
            'comercio_city_user_id' => $colman->id,
            'seller_id'             => null,
        ]);
    }

    function colman() {
        $user = User::where('company_name', 'colman')->first();
        Client::create([
            'name'              => 'Lucas ResIns',
            'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
            'cuit'              => '20242112025',
            'razon_social'      => 'MARCOS SRL', 
            'price_type_id'     => 1,
            'iva_condition_id'  => 1,
            'user_id'           => $user->id,
            'seller_id'         => null,
        ]);
        Client::create([
            'name'              => 'Marcos MONO',
            'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
            'cuit'              => '20242112025',
            'razon_social'      => 'MARCOS SRL', 
            'price_type_id'     => 2,
            'iva_condition_id'  => 2,
            'user_id'           => $user->id,
            'seller_id'         => null,
        ]);
        Client::create([
            'name'              => 'Marcos ConsFinal',
            'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
            'cuit'              => '20242112025',
            'razon_social'      => 'MARCOS SRL', 
            'price_type_id'     => 2,
            'iva_condition_id'  => 3,
            'user_id'           => $user->id,
            'seller_id'         => null,
        ]);
        Client::create([
            'name'              => 'Marcos nada',
            'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
            'cuit'              => '20242112025',
            'razon_social'      => 'MARCOS SRL', 
            'price_type_id'     => 2,
            'iva_condition_id'  => null,
            'user_id'           => $user->id,
            'seller_id'         => null,
        ]);
    }
}
