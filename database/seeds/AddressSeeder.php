<?php

use App\Address;
use App\Buyer;
use App\User;
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
        $buyers = Buyer::all();
        foreach ($buyers as $buyer) {
            Address::create([
                'street'        => 'Carmen Gadea',
                'street_number' => '787',
                'city'          => 'Gualeguay',
                'province'      => 'Entre Rios',
                'lat'           => '-33.146681',
                'lng'           => '-59.309596',
                'buyer_id'      => $buyer->id
            ]);
            Address::create([
                'street'        => 'Chacabuco',
                'street_number' => '989',
                'city'          => 'Gualeguay',
                'province'      => 'Entre Rios',
                'lat'           => '-33.146681',
                'lng'           => '-59.309596',
                'buyer_id'      => $buyer->id
            ]);
        }

        // Commerce
        $commerces = User::where('company_name', 'Fiushh')
                            ->orWhere('company_name', 'Pinocho')
                            ->orWhere('company_name', 'CandyGuay')
                            ->orWhere('company_name', 'kas aberturas')
                            // ->orWhere('company_name', 'colman')
                            ->get();
        foreach ($commerces as $commerce) {
            Address::create([
                'street'        => 'Carmen Gadea',
                'street_number' => '787',
                'city'          => 'Gualeguay',
                'province'      => 'Entre Rios',
                'lat'           => '-33.146681',
                'lng'           => '-59.309596',
                'user_id'       => $commerce->id,
            ]);
            Address::create([
                'street'        => 'Chacabuco',
                'street_number' => '989',
                'city'          => 'Gualeguay',
                'province'      => 'Entre Rios',
                'lat'           => '-33.146681',
                'lng'           => '-59.309596',
                'user_id'       => $commerce->id,
            ]);
        }
    }
}
