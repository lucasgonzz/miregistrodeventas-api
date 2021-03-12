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
            Client::create([
                'name' => 'Mio',
                'user_id' => $user_id,
            ]);
            Client::create([
                'name' => 'Juan',
                'user_id' => $user_id,
                'seller_id' => 1
            ]);
            Client::create([
                'name' => 'Marcos',
                'user_id' => $user_id,
                'seller_id' => 1
            ]);
            Client::create([
                'name' => 'Lucas',
                'user_id' => $user_id,
                'seller_id' => 1
            ]);
            Client::create([
                'name' => 'Luquis',
                'user_id' => $user_id,
                'seller_id' => 2
            ]);
            Client::create([
                'name' => 'Marta',
                'user_id' => $user_id,
                'seller_id' => 2
            ]);
            Client::create([
                'name' => 'Juana',
                'user_id' => $user_id,
                'seller_id' => 2
            ]);
        }

    }
}
