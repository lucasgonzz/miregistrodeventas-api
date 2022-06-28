<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Category;
use App\SubCategory;

class SubCategorySeeder extends Seeder
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
        $user = User::where('company_name', 'Pinocho')
                    ->first();
        $categories = Category::where('user_id', $user->id)
                                ->get();
        foreach ($categories as $category) {
            $names = [];
            if ($category->name == 'Juguetes infantiles') {
                $names = ['sonajeros'];
            } else if ($category->name == 'INFLABLES') {
                $names = ['personajes', 'homrigas'];
            } else if ($category->name == 'Auriculares con muchas cosas') {
                $names = ['Casco', 'Comunes'];
            } 
            for ($i=0; $i < count($names); $i++) {
                $sub_category = SubCategory::create([
                    'name'        => $names[$i],
                    'category_id' => $category->id,
                    'user_id' => $user->id,
                ]);         
            }
        }
    }
}
 