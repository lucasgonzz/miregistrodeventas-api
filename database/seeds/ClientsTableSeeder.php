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
        $provider = User::where('company_name', 'Lucas')->first();
        for ($user_id=301; $user_id <= 304 ; $user_id++) { 
            // for ($i=1; $i < 10; $i++) { 
                // Client::create([
                //     'name' => 'Mio',
                //     'user_id' => $user_id,
                // ]);
                // Client::create([
                //     'name' => 'Juan',
                //     'surname' => 'apellido de juan',
                //     'address' => 'calle 123 al 777',
                //     'user_id' => $user_id,
                //     'seller_id' => $user_id == $provider->id ? 1 : null
                // ]);
                Client::create([
                    'name'              => 'Marcos',
                    'surname'           => 'Gonzalez',
                    'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                    'cuit'              => '20242112025',
                    'razon_social'      => 'MARCOS SRL', 
                    'iva_condition_id'  => 1,
                    'user_id'           => $user_id,
                    'seller_id'         => $user_id == $provider->id ? 1 : null
                ]);
                Client::create([
                    'name'              => 'Lucas',
                    'surname'           => 'Gonzalez',
                    'email'             => 'lucasgonzalez5500@gmail.com',
                    'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                    'cuit'              => '20242112025',
                    'razon_social'      => 'MARCOS SRL', 
                    'iva_condition_id'  => 1,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == $provider->id ? 1 : null
                ]);
                Client::create([
                    'name'              => 'Luquis',
                    'surname'           => 'Gonzalez',
                    'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                    'cuit'              => '20242112025',
                    'razon_social'      => 'MARCOS SRL', 
                    'iva_condition_id'  => 1,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == $provider->id ? 2 : null
                ]);
                Client::create([
                    'name'              => 'Marta',
                    'surname'           => 'Gonzalez',
                    'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                    'cuit'              => '20242112025',
                    'razon_social'      => 'MARCOS SRL', 
                    'iva_condition_id'  => 1,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == $provider->id ? 2 : null
                ]);
                Client::create([
                    'name'              => 'Juana',
                    'surname'           => 'Gonzalez',
                    'address'           => 'San antonio 23 - Gualeguay, Entre Rios',
                    'cuit'              => '20242112025',
                    'razon_social'      => 'MARCOS SRL', 
                    'iva_condition_id'  => 1,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == $provider->id ? 2 : null
                ]);
            // }
        }

    }
}
