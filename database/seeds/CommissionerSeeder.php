<?php

use App\Commissioner;
use App\User;
use Illuminate\Database\Seeder;

class CommissionerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$mayorista = User::where('company_name', 'Lucas')
    						->first();
        $user_id = $mayorista->id;
        Commissioner::create([
        	'name' 		 => 'Oscar',
        	'percentage' => 2,
        	'user_id' => $user_id,
        ]);
        Commissioner::create([
        	'name' 		 => 'Fede',
        	'percentage' => 1,
        	'user_id' => $user_id,
        ]);
        Commissioner::create([
            'name'       => 'Papi',
            'percentage' => 1,
            'user_id' => $user_id,
        ]);
        Commissioner::create([
            'name'       => 'Perdidas',
            'percentage' => 5,
            'user_id' => $user_id,
            // 'seller_id' => 0,
        ]);
    }
}
