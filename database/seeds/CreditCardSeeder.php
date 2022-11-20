<?php

use App\CreditCard;
use App\User;
use Illuminate\Database\Seeder;

class CreditCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('company_name', 'colman')->get();
        $models = [
            [
                'name' => 'Visa',
            ],
            [
                'name' => 'MasterCard',
            ],
        ];
        foreach ($users as $user) {
            foreach ($models as $model) {
                CreditCard::create([
                    'name' => $model['name'],
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
