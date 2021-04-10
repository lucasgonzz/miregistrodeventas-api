<?php

use Illuminate\Database\Seeder;
use App\Client;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($user_id=1; $user_id <= 2 ; $user_id++) { 
            for ($i=1; $i < 40; $i++) { 
                Client::create([
                    'name' => 'Mio '.$i,
                    'user_id' => $user_id,
                ]);
                Client::create([
                    'name' => 'Juan con apellido '.$i,
                    'surname' => 'apellido de juan '.$i,
                    'address' => 'calle 123 al 7'.$i,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == 1 ? 1 : null
                ]);
                Client::create([
                    'name' => 'Marcos '.$i,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == 1 ? 1 : null
                ]);
                Client::create([
                    'name' => 'Lucas '.$i,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == 1 ? 1 : null
                ]);
                Client::create([
                    'name' => 'Luquis '.$i,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == 1 ? 2 : null
                ]);
                Client::create([
                    'name' => 'Marta '.$i,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == 1 ? 2 : null
                ]);
                Client::create([
                    'name' => 'Juana '.$i,
                    'user_id' => $user_id,
                    'seller_id' => $user_id == 1 ? 2 : null
                ]);
            }
        }

    }
}
