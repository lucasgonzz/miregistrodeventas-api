<?php

use App\Condition;
use Illuminate\Database\Seeder;

class ConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $conditions = [
            [
                'name' => 'Nuevo',
                'description' => 'Equipo nuevo sellado de fabrica.',
            ],
            [
                'name' => 'Seminuevo',
                'description' => 'Equipo reacondicionado por Apple, con bateria +80%.',
            ],
            [
                'name' => 'Usado',
                'description' => 'Equipo usado.',
            ],
        ];
        foreach ($conditions as $condition) {
            Condition::create([
                'name' => $condition['name'],
                'description' => $condition['description'],
            ]);
        }
    }
}
