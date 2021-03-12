<?php

use Illuminate\Database\Seeder;
use App\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $marcos = App\User::where('name', 'Mi Negocio')->first();
        Category::create([
            'name'    => 'Ropa',
            'user_id' => $marcos->id,
        ]);
        Category::create([
            'name'    => 'Celulares',
            'user_id' => $marcos->id,
        ]);
        Category::create([
            'name'    => 'Muebles',
            'user_id' => $marcos->id,
        ]);
        Category::create([
            'name'    => 'Camping',
            'user_id' => $marcos->id,
        ]);
        Category::create([
            'name'    => 'Escuela',
            'user_id' => $marcos->id,
        ]);
        Category::create([
            'name'    => 'Tecnologia',
            'user_id' => $marcos->id,
        ]);
        Category::create([
            'name'    => 'Peluches',
            'user_id' => $marcos->id,
        ]);
        Category::create([
            'name'    => 'Calzado',
            'user_id' => $marcos->id,
        ]);
    }
}
