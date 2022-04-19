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
        $users = User::where('company_name', 'Fiushh')
                    ->orWhere('company_name', 'Pinocho')
                    ->get();
        $categories = Category::where('user_id', $users[0]->id)
                                ->get();
        foreach ($users as $user) {
            foreach ($categories as $category) {
                if ($category->name == 'Celulares') {
                    $names = ['Iphone'];
                } else if ($category->name == 'Cargadores') {
                    $names = ['Iphone', 'Android'];
                } else if ($category->name == 'Auriculares') {
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
        
        $user = User::where('name', 'Mi Negocio')->first();
        $categories = Category::where('user_id', $user->id)
                                ->get();
        foreach ($categories as $category) {
            for ($i=1; $i <= 5; $i++) { 
                $sub_category = SubCategory::create([
                    'name'        => $category->name.' sub '.$i,
                    'category_id' => $category->id,
                    'user_id' => $user->id,
                ]);         
            }
        }
    }
}
