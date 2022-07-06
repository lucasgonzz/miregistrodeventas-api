<?php

use App\Location;
use App\User;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->kasAberturas();
    }

    function kasAberturas() {
        $user = User::where('company_name', 'kas aberturas')->first();
        $locations = ['Gualeguay', 'Victoria'];
        foreach ($locations as $location) {
            Location::create([
                'name'      => $location,
                'user_id'   => $user->id,
            ]); 
        }
    }
}
