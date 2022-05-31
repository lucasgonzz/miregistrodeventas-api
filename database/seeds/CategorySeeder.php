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
        $categories = ['Ropa', 'Celulares', 'Muebles', 'Camping', 'Escuela', 'Tecnologia', 'Peluches', 'Calzado'];
        $users = User::where('company_name', 'Fiushh')
                    ->orWhere('company_name', 'Pinocho')
                    ->get();
        $categories = ['Celulares', 'Cargadores', 'Auriculares'];
        foreach ($users as $user) {
            foreach ($categories as $category) {
                Category::create([
                    'name'    => $category,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
