<?php

use App\Iva;
use Illuminate\Database\Seeder;

class IvaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ivas = ['Responsable inscripto', 'Monotributista', 'Consumidor final'];
        foreach ($ivas as $iva) {
            Iva::create([
                'name' => $iva,
            ]);
        }
    }
}
