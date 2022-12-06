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
        $users = User::where('company_name', 'pinocho')
                        ->orWhere('company_name', 'kas aberturas')
                        ->orWhere('company_name', 'nebulaStore')
                        ->orWhere('company_name', 'colman')
                        ->orWhere('company_name', 'la barraca')
                        ->get();
        foreach ($users as $user) {
            $lucas = Buyer::create([
                'name'      => 'Lucas',
                'surname'   => 'Gonzalez',
                'city'      => 'Gualeguay',
                'phone'     => '+5493444622139',
                'email'     => 'lucasgonzalez5500@gmail.com',
                'password'  => bcrypt('1234'),
                'user_id'   => $user->id,
            ]);
        }
    }
}
