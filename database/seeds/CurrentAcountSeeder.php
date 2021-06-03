<?php

use App\CurrentAcount;
use Illuminate\Database\Seeder;

class CurrentAcountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $current_acount = CurrentAcount::create([
            'detalle'   => 'Saldo inicial',
            'status'    => 'sin_pagar',
            'client_id' => 2,
            'debe'      => 1000,
            'saldo'     => 1000,
        ]);
    }
}
