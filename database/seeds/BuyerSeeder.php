<?php

use App\Buyer;
use Illuminate\Database\Seeder;

class BuyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($user_id=302; $user_id < 304; $user_id++) { 
            for ($i=0; $i < 10; $i++) { 
                $lucas = Buyer::create([
                    'name' => 'Lucas '.$i,
                    'surname' => 'Gonzalez',
                    'city' => 'Gualeguay',
                    'phone' => '+549344462213'.$i,
                    'email' => 'lucasgonzalez5500@gmail.com',
                    'password' => bcrypt('1234'),
                    'user_id' => $user_id,
                ]);
                // Buyer::create([
                //     'name' => 'Marcos '.$i,
                //     'surname' => 'Gonzalez',
                //     'city' => 'Gualeguay',
                //     'phone' => '+549344461213'.$i,
                //     'email' => 'Lucas@gmail.com',
                //     'password' => bcrypt('1234'),
                //     'user_id' => $user_id,
                // ]);
            }
        }
    }
}
