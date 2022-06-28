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
        $this->pinocho();
    }

    function pinocho() {
        $user = User::where('company_name', 'pinocho')->first();
        $categories = ['Juguetes infantiles', 'INFLABLES', 'Auriculares con muchas cosas', 'categoria para rellenar mas que nada', 'segunda categoria', 'otra cateogoria mas', 'otra', 'y otra', 'y otra mas'];
        foreach ($categories as $category) {
            Category::create([
                'name'    => $category,
                'user_id' => $user->id,
            ]);
        }
    }
}
