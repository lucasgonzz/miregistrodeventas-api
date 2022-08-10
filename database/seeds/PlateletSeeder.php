<?php

use App\Platelet;
use App\User;
use Illuminate\Database\Seeder;

class PlateletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('company_name', 'Fiushh')
                    ->orWhere('company_name', 'Pinocho')
                    ->orWhere('company_name', 'nebulaStore')
                    ->orWhere('company_name', 'kas aberturas')
                    ->get();
        $platelets = [
            [
                'name' => 'Envios a todo Argentina',
                'description'  => 'Dependiendo tu zona calculamos el envio'
            ],
            [
                'name' => 'PAGA SIMPLE Y RAPIDO',
                'description'  => 'Tarjetas de Crédito y Debito con Mercado Pago'
            ],
            [
                'name' => 'COMPRÁ CON SEGURIDAD',
                'description'  => 'Tus datos siempre están protegidos'
            ],
        ];
        foreach ($users as $user) {
            foreach ($platelets as $platelet) {
                Platelet::create([
                    'name'     => $platelet['name'],
                    'description'      => $platelet['description'],
                    'user_id'   => $user->id,
                ]);
            }
        }
    }
}
