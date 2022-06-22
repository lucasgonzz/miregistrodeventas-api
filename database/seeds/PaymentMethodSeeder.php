<?php

use App\PaymentMethod;
use App\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pinocho = User::where('company_name', 'pinocho')->first();
        $payment_methods = [
            [
                'name' => 'MercadoPago',
                'description' => 'Paga Online con tu cuenta de MercadoPago',
            ],
            [
                'name' => 'Contado',
                'description' => '',
            ],
            [
                'name' => 'A convenir',
                'description' => 'Podes pagar con modo, billetera Santa Fe',
            ],
        ];
        foreach ($payment_methods as $payment_method) {
            PaymentMethod::create([
                'name'          => $payment_method['name'],
                'description'   => $payment_method['description'],
                'user_id'       => $pinocho->id,
            ]);
        }
    }
}
