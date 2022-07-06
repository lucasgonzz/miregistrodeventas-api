<?php

use App\Commissioner;
use App\Seller;
use App\User;
use Illuminate\Database\Seeder;

class SellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('company_name', 'kas aberturas')
        			->first();
                    
        $user_id = $user->id;
        // $user_id = 2;
        $potro = Seller::create([
        	'name' 		 => 'Potro',
        	'surname' 	 => 'Apellido del Potro',
        	'commission' => 10,
        	'user_id' 	 => $user_id,
        ]);	
        Commissioner::create([
            'name'       => 'Potro',
            'seller_id' => $potro->id,
            'user_id'    => $user_id,
        ]);
        $bocha = Seller::create([
            'name'       => 'Bocha',
            'surname'    => 'Apellido del Bocha',
            'commission' => 10,
            'user_id'    => $user_id,
            'seller_id'  => $potro->id,
        ]); 
        Commissioner::create([
            'name'       => 'Bocha',
            'seller_id' => $bocha->id,
            'user_id'    => $user_id,
        ]);
        $ramiro = Seller::create([
            'name'       => 'Ramiro',
            'commission' => 10,
            'user_id'    => $user_id,
        ]); 
        Commissioner::create([
            'name'       => 'Ramiro',
            'seller_id' => $ramiro->id,
            'user_id'    => $user_id,
        ]);
    }
}
