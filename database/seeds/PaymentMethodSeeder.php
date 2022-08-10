<?php

use App\Credential;
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
        $users = User::where('company_name', 'pinocho')
                        ->orWhere('company_name', 'kas aberturas')
                        ->orWhere('company_name', 'nebulaStore')
                        ->get();
        $payment_methods = [
            [
                'name' => 'MercadoPago',
                'description' => 'Paga Online con tu cuenta de MercadoPago',
                'payment_method_type_id' => 1,
                'public_key' => 'TEST-55fdbf12-f638-48a1-a6fe-1dd41c771384',
                'access_token' => 'TEST-3668585670354328-100112-a353cb99b53860f22fdf7e7b87c4fd8b-163250661',
            ],
            [
                'name' => 'Contado',
                'description' => '',
                'public_key' => '',
                'access_token' => '',
                'payment_method_type_id' => null,
            ],
            [
                'name' => 'A convenir',
                'description' => 'Podes pagar con modo, billetera Santa Fe',
                'public_key' => '',
                'access_token' => '',
                'payment_method_type_id' => null,
            ],
        ];
        foreach ($users as $user) {
            foreach ($payment_methods as $payment_method) {
                $_payment_method = PaymentMethod::create([
                    'name'                      => $payment_method['name'],
                    'description'               => $payment_method['description'],
                    'public_key'                => $payment_method['public_key'],
                    'access_token'              => $payment_method['access_token'],
                    'payment_method_type_id'    => $payment_method['payment_method_type_id'],
                    'user_id'                   => $user->id,
                ]);
            }
        }
    }
}
