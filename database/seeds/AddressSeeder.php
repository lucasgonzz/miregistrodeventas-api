<?php

use App\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Address::create([
            'street' => 'Carmen Gadea',
            'street_number' => '787',
            'lat' => '-33.146681',
            'lng' => '-59.309596',
            'buyer_id' => 19
        ]);
        Address::create([
            'street' => 'Chacabuco',
            'street_number' => '989',
            'lat' => '-33.146681',
            'lng' => '-59.309596',
            'buyer_id' => 19
        ]);
    }
}
