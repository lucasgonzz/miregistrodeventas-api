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
        Buyer::create([
    		'name' => 'Lucas',
    		'surname' => 'Gonzalez',
    		'city' => 'Gualeguay',
            'address' => 'Pellegrini',
            'address_number' => '876',
    		'phone' => '3444622139',
    		// 'email' => 'Lucas@gmail.com',
    		'password' => bcrypt('1234'),
            'user_id' => 2,
        ]);
    }
}
