<?php

use App\Extencion;
use Illuminate\Database\Seeder;

class ExtencionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $extencions = ['produccion'];
        foreach ($extencions as $extencion) {
            Extencion::create([
                'name' => $extencion
            ]);
        }
    }
}
