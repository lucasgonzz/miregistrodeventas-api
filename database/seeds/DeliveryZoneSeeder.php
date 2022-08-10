<?php

use App\DeliveryZone;
use App\User;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
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
                'name' => 'Rosario',
                'description' => 'Rosario y alrededores',
                'price' => 500,
            ],
            [
                'name' => 'Todo el Pais',
                'description' => null,
                'price' => 800,
            ],
        ];
        foreach ($users as $user) {
            foreach ($models as $model) {
                DeliveryZone::create([
                    'name'          => $model['name'],
                    'description'   => $model['description'],
                    'price'         => $model['price'],
                    'user_id'       => $user->id,
                ]);
            }
        }
    }
}
