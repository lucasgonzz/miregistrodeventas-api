<?php

use App\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuration::create([
        	'buyer_id' => 1
        ]);
    }
}
