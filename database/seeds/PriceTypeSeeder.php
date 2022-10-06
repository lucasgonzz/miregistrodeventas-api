<?php

use App\PriceType;
use App\User;
use Illuminate\Database\Seeder;

class PriceTypeSeeder extends Seeder
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
                'name'          => 'Mayorista',
                'percentage'    => 10,
                'position'      => 1,
            ],
            [
                'name'          => 'Comercio',
                'percentage'    => 15,
                'position'      => 2,
            ],
            [
                'name'          => 'Consumidor final',
                'percentage'    => 10,
                'position'      => 3,
            ],
        ];
        $users = User::where('company_name', 'colman')
                        ->get();
        foreach ($users as $user) {
            foreach ($models as $model) {
                PriceType::create([
                    'name'          => $model['name'],
                    'percentage'    => $model['percentage'],
                    'position'      => $model['position'],
                    'user_id'       => $user->id,
                ]);
            }
        }
    }
}
