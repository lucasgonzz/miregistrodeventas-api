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
                'user_id' => 302
            ],
            [
                'name' => 'Seminuevo',
                'description' => 'Equipo reacondicionado por Apple, con bateria +80%.',
                'user_id' => 302
            ],
            [
                'name' => 'Usado',
                'description' => 'Equipo usado.',
                'user_id' => 302
            ],
        ];
        foreach ($conditions as $condition) {
            Condition::create([
                'name' => $condition['name'],
                'description' => $condition['description'],
                'user_id' => $condition['user_id'],
            ]);
        }
    }
}
