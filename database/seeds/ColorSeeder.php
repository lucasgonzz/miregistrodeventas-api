<?php

use App\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $colors = [
            ['name' => 'Rojo', 'value' => '#FF0000'],
            ['name' => 'Violeta', 'value' => '#6f42c1'],
            ['name' => 'Azul', 'value' => '#0000FF'],
            ['name' => 'Rosado', 'value' => '#e83e8c'],
            ['name' => 'Naranja', 'value' => '#fd7e14'],
            ['name' => 'Amarillo', 'value' => '#ffc107'],
            ['name' => 'Dorado', 'value' => '#EABE3F'],
            ['name' => 'Blanco', 'value' => '#ffffff'],
            ['name' => 'Negro', 'value' => '#000000'],
            ['name' => 'Grafito', 'value' => '#464545'],
            ['name' => 'Silver', 'value' => '#C0C0C0'],
            ['name' => 'Verde', 'value' => '#28a745'],
        ];
        foreach ($colors as $color) {
            Color::create([
                'name' => $color['name'],
                'value' => $color['value'],
            ]);
        }
    }
}
