<?php

use App\Surchage;
use App\User;
use Illuminate\Database\Seeder;

class SurchageSeeder extends Seeder
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
                    ->orWhere('company_name', 'la barraca')
                    ->get();

        foreach ($users as $user) {
            Surchage::create([
                'name'       => 'Iva 21',
                'percentage' => 21,
                'user_id'    => $user->id,
            ]);
            Surchage::create([
                'name'       => 'Envio',
                'percentage' => 50,
                'user_id'    => $user->id,
            ]);
        }
    }
}
