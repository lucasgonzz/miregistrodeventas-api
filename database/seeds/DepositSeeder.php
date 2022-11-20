<?php

use App\Deposit;
use App\User;
use Illuminate\Database\Seeder;

class DepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $models = [
            [
                'name'          => 'Nave',
                'description'   => 'Este es el mas importante',
            ],
            [
                'name'          => 'Fila',
                'description'   => '',
            ],
            [
                'name'          => 'Columna',
                'description'   => '',
            ],
        ];
        $user = User::where('company_name', 'colman')->first();
        foreach ($models as $model) {
            Deposit::create([
                'name'      => $model['name'],
                'user_id'   => $user->id,
            ]);
        }
    }
}
