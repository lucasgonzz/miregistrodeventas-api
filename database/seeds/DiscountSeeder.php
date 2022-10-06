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
        $users = User::where('company_name', 'kas aberturas')
                    ->orWhere('company_name', 'colman')
        			->get();

        foreach ($users as $user) {
            Discount::create([
            	'name' 		 => 'Personajes',
            	'percentage' => 10,
            	'user_id'    => $user->id,
            ]);
            Discount::create([
            	'name' 		 => 'Escolares',
            	'percentage' => 20,
            	'user_id'    => $user->id,
            ]);
        }
    }
}
