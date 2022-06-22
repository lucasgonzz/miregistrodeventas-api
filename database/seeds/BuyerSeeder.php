<?php

use App\Buyer;
use App\User;
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
        $pinocho = User::where('company_name', 'pinocho')->first();
        $lucas = Buyer::create([
            'name'      => 'Lucas',
            'surname'   => 'Gonzalez',
            'city'      => 'Gualeguay',
            'phone'     => '+5493444622139',
            'email'     => 'lucasgonzalez5500@gmail.com',
            'password'  => bcrypt('1234'),
            'user_id'   => $pinocho->id,
        ]);
    }
}
