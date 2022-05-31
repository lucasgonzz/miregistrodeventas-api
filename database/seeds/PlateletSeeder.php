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
                    ->get();
        $platelets = [
            [
                'title' => 'Envios a todo Argentina',
                'text'  => 'Dependiendo tu zona calculamos el envio'
            ],
            [
                'title' => 'PAGA SIMPLE Y RAPIDO',
                'text'  => 'Tarjetas de Crédito y Debito con Mercado Pago'
            ],
            [
                'title' => 'COMPRÁ CON SEGURIDAD',
                'text'  => 'Tus datos siempre están protegidos'
            ],
        ];
        foreach ($users as $user) {
            foreach ($platelets as $platelet) {
                Platelet::create([
                    'title'     => $platelet['title'],
                    'text'      => $platelet['text'],
                    'user_id'   => $user->id,
                ]);
            }
        }
    }
}
