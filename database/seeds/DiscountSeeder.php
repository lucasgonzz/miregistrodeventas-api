<?php

use App\Discount;
use App\User;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
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
        Discount::create([
        	'name' 		 => 'Personajes',
        	'percentage' => 10,
        	'user_id'    => $user_id,
        ]);
        Discount::create([
        	'name' 		 => 'Escolares',
        	'percentage' => 20,
        	'user_id'    => $user_id,
        ]);
    }
}
