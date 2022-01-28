<?php

use App\Category;
use App\User;
use Illuminate\Database\Seeder;

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
        $user = User::where('company_name', 'Fiushh')->first();
        $categories = ['Celulares', 'Cargadores', 'Auriculares'];
        $icon_id = 1;
        foreach ($categories as $category) {
            Category::create([
                'name'    => $category,
                'icon_id' => $icon_id,
                'user_id' => $user->id,
            ]);
            $icon_id++;
        }
    }
}
