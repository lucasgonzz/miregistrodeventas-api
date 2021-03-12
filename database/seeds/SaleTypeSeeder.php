<?php

use App\SaleType;
use App\User;
use Illuminate\Database\Seeder;

class SaleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$mayorista = User::where('company_name', 'Lucas')->first();
        $user_id = $mayorista->id;
        SaleType::create([
        	'name' => 'Normal',
        	'user_id' => $user_id,
        ]);
        SaleType::create([
        	'name' => 'Varios',
        	'user_id' => $user_id,
        ]);
    }
}
