<?php

use Illuminate\Database\Seeder;
use App\Size;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($size=30; $size < 50; $size++) { 
            Size::create([
                'value' => $size,
            ]);
        }
        $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'];
        foreach ($sizes as $size) {
            Size::create([
                'value' => $size,
            ]);
        }
    }
}
