<?php

use App\OrderProductionStatus;
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
        $statuses = ['Deposito', 'Producción', 'Entrega', 'Colocación'];

        foreach ($statuses as $status) {
            OrderProductionStatus::create([
                'name' => $status,
            ]);            
        } 
    }
}
