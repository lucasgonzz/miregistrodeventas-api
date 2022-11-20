<?php

use App\CreditCard;
use App\CreditCardPaymentPlan;
use App\User;
use Illuminate\Database\Seeder;

class CreditCardPaymentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('company_name', 'colman')->first();
        $credit_cards = CreditCard::where('user_id', $user->id)->get();
        $models = [
            [
                'installments'  => 3,
                'surchage'      => 50,
            ],
            [
                'installments'  => 6,
                'surchage'      => 100,
            ],
        ];
        foreach ($credit_cards as $credit_card) {
            foreach ($models as $model) {
                CreditCardPaymentPlan::create([
                    'installments' => $model['installments'],
                    'surchage' => $model['surchage'],
                    'credit_card_id'    => $credit_card->id,
                ]);               
            }
        }
    }
}
