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
            ['name' => 'Red', 'value' => '#FF0000'],
            ['name' => 'Purple', 'value' => '#6f42c1'],
            ['name' => 'Blue', 'value' => '#0000FF'],
            ['name' => 'Pink', 'value' => '#e83e8c'],
            ['name' => 'Orange', 'value' => '#fd7e14'],
            ['name' => 'Coral', 'value' => '#ff7f50'],
            ['name' => 'Yellow', 'value' => '#ffc107'],
            ['name' => 'Golden', 'value' => '#EABE3F'],
            ['name' => 'White', 'value' => '#ffffff'],
            ['name' => 'Black', 'value' => '#000000'],
            ['name' => 'Graphite', 'value' => '#464545'],
            ['name' => 'Silver', 'value' => '#C0C0C0'],
            ['name' => 'Green', 'value' => '#28a745'],

        ];
        foreach ($colors as $color) {
            Color::create([
                'name' => $color['name'],
                'value' => $color['value'],
            ]);
        }
    }
}
