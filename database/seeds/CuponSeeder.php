<?php

use App\Cupon;
use App\User;
use Illuminate\Database\Seeder;

class CuponSeeder extends Seeder
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
                        ->get();
        $models = [
            [
                'amount'     => 10,
                'percentage' => null,
                'min_amount' => null,
                'code'       => '10',
            ],
            [
                'amount'     => 20,
                'percentage' => null,
                'min_amount' => null,
                'code'       => '20',
            ],
            [
                'amount'     => 300,
                'percentage' => null,
                'min_amount' => null,
                'code'       => '300',
            ],
            [
                'amount'     => null,
                'percentage' => 50,
                'min_amount' => null,
                'code'       => '50por',
            ],
            [
                'amount'     => 10,
                'percentage' => null,
                'min_amount' => 1000,
                'code'       => '1000minimo',
            ],
        ];

        foreach ($users as $user) {
            foreach ($models as $model) {
                Cupon::create([
                    'amount'        => $model['amount'],
                    'percentage'    => $model['percentage'],
                    'min_amount'    => $model['min_amount'],
                    'code'          => $model['code'],
                    'user_id'       => $user->id,
                ]);
            }
        }
    }
}
