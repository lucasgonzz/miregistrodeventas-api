<?php

use App\Budget;
use App\Client;
use App\Http\Controllers\Helpers\BudgetHelper;
use App\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('company_name', 'KAS Aberturas')
                            ->first();
        $client = Client::where('user_id', $user->id)
                        ->where('name', 'lucas')->first();

        $products = [
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS, MEDIDAS 2.00 X 2.05 CON VIDRIO DVH 4+9+4',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS, MEDIDAS 2.00 X 2.05 CON VIDRIO DVH 4+9+4',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS, MEDIDAS 2.00 X 2.05 CON VIDRIO DVH 4+9+4',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS, MEDIDAS 2.00 X 2.05 CON VIDRIO DVH 4+9+4',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS, MEDIDAS 2.00 X 2.05 CON VIDRIO DVH 4+9+4 CON MUCHAS COSAS MAS COMO PARA RELLENAR',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS, MEDIDAS 2.00 X 2.05 CON VIDRIO DVH 4+9+4 CON MUCHAS COSAS MAS COMO PARA RELLENAR',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
            [
                'bar_code'  => '',
                'amount'    => 2,
                'name'      => 'PUERTA BALCON EN ALUMINIO BLANCO - 2 PAÑOS CORREDIZOS, MEDIDAS 2.00 X 2.05 CON VIDRIO DVH 4+9+4 CON MUCHAS COSAS MAS COMO PARA RELLENAR',
                'price'     => 77000.5,
                'bonus'     => null,
            ],
        ];

        $observations = [
            ['text' => 'Tenes 20% de descuentos con tarjetas VISA y MASTERCARD'],
            ['text' => '6 cuotas sin interes'],
            ['text' => 'DESCRIPCION SUMAMENTE LARGA COMO PARA RELLENAR VARIOS ESPACIOS EN EL PDF DE TODO LO QUE VIENE DADO POR LA VIDA'],
        ];

        $budget = Budget::create([
            'client_id' => $client->id,
            'num'       => 1,
            'start_at'  => Carbon::now()->addWeeks(4),
            'finish_at' => Carbon::now()->addWeeks(5),
            'user_id'   => $user->id,
        ]);
        BudgetHelper::attachProducts($budget, $products);
        BudgetHelper::attachObservations($budget, $observations);
        return;
        $budget = Budget::create([
            'client_id'                 => $clients[1]->id,
            'num'                       => 2,
            'start_at'                  => Carbon::now()->addWeeks(4),
            'finish_at'                 => Carbon::now()->addWeeks(5),
            'delivery_and_placement'    => 1,
            'user_id'                   => $user->id,
        ]);
        BudgetHelper::attachProducts($budget, $products);
        BudgetHelper::attachObservations($budget, $observations);
    }
}
