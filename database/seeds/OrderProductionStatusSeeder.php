<?php

use App\OrderProductionStatus;
use App\User;
use Illuminate\Database\Seeder;

class OrderProductionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kas = User::where('company_name', 'kas aberturas')->first();
        $colman = User::where('company_name', 'colman')->first();
        $mc_electronica = User::where('company_name', 'mc electronica')->first();

        $statuses = [
            ['name' => 'Deposito', 'optional' => false, 'position' => 1, 'user_id' => $kas->id], 
            ['name' => 'Producción', 'optional' => false, 'position' => 2, 'user_id' => $kas->id], 
            ['name' => 'Entrega', 'optional' => false, 'position' => 3, 'user_id' => $kas->id], 
            ['name' => 'Colocación', 'optional' => true, 'position' => 4, 'user_id' => $kas->id],
            ['name' => 'Pintura', 'optional' => true, 'position' => 5, 'user_id' => $kas->id],

            ['name' => 'MONTAJE', 'optional' => false, 'position' => 1, 'user_id' => $colman->id], 
            ['name' => 'DEPOSITO', 'optional' => false, 'position' => 2, 'user_id' => $colman->id], 
            ['name' => 'ARMADORES', 'optional' => false, 'position' => 3, 'user_id' => $colman->id], 
            ['name' => 'EMBALAJE', 'optional' => false, 'position' => 4, 'user_id' => $colman->id],
            ['name' => 'TERMINADO', 'optional' => false, 'position' => 5, 'user_id' => $colman->id],

            ['name' => 'MONTAJE', 'optional' => false, 'position' => 1, 'user_id' => $mc_electronica->id], 
            ['name' => 'DEPOSITO', 'optional' => false, 'position' => 2, 'user_id' => $mc_electronica->id], 
            ['name' => 'ARMADORES', 'optional' => false, 'position' => 3, 'user_id' => $mc_electronica->id], 
            ['name' => 'EMBALAJE', 'optional' => false, 'position' => 4, 'user_id' => $mc_electronica->id],
            ['name' => 'TERMINADO', 'optional' => false, 'position' => 5, 'user_id' => $mc_electronica->id],
        ];

        foreach ($statuses as $model) {
            OrderProductionStatus::create([
                'name'      => $model['name'],
                'optional'  => $model['optional'],
                'position'  => $model['position'],
                'user_id'   => $model['user_id'],
            ]);            
        } 

    }
}
