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

        $this->nebulaStore();

        $this->kasAberturas();

        $this->colman();
    }

    function pinocho() {
        $user = User::where('company_name', 'Pinocho')
                    ->first();
        $categories = Category::where('user_id', $user->id)
                                ->orderBy('id', 'ASC')
                                ->get();
        foreach ($categories as $category) {
            $names = [];
            if ($category->name == 'Auriculares con muchas cosas') {
                $names = ['Casco', 'Comunes'];
            } else if ($category->name == 'INFLABLES') {
                $names = ['personajes', 'homrigas'];
            }  else if ($category->name == 'Juguetes infantiles') {
                $names = ['sonajeros'];
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

    function nebulaStore() {
        $user = User::where('company_name', 'nebulaStore')
                    ->first();
        $categories = Category::where('user_id', $user->id)
                                ->get();
        foreach ($categories as $category) {
            $names = [];
            if ($category->name == 'Camperas') {
                $names = ['Inflables', 'De lana'];
            } else if ($category->name == 'Pantalones') {
                $names = ['Jeans', 'De cuero'];
            } else if ($category->name == 'Zapatillas') {
                $names = ['Deportivas', 'Urbanas'];
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

    function kasAberturas() {
        $user = User::where('company_name', 'kas aberturas')
                    ->first();
        $categories = Category::where('user_id', $user->id)
                                ->get();
        foreach ($categories as $category) {
            $names = [];
            if ($category->name == 'puertas') {
                $names = ['de exterior'];
            } else if ($category->name == 'repuestos') {
                $names = ['industriales', 'de otras cosas'];
            } else if ($category->name == 'visagras') {
                $names = ['puertas', 'portones'];
            }  else if ($category->name == 'calefactores') {
                $names = ['nacionales', 'importados'];
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

    function colman() {
        $user = User::where('company_name', 'colman')
                    ->first();
        $categories = Category::where('user_id', $user->id)
                                ->get();
        foreach ($categories as $category) {
            $names = [];
            if ($category->name == 'Lava ropas') {
                
                $names = ['lavarropa nuevo', 'lavarropas usados'];

            } else if ($category->name == 'Aires acondicionados') {
                
                $names = ['aire nuevo', 'aires acondicionados usados'];

            }  else if ($category->name == 'Computacion') {
                
                $names = ['computacion 1', 'computacion 2'];

            }  else if ($category->name == 'Tanques de oxigeno') {
                
                $names = ['Tanques de oxigeno 1', 'Tanques de oxigeno 2'];

            }  else if ($category->name == 'cosas para la casa') {
                
                $names = ['cosas para la casa 1', 'cosas para la casa 2'];

            }   else if ($category->name == 'Repuestos de lavarropas') {
                
                $names = ['Repuestos de lavarropas 1', 'Repuestos de lavarropas 2'];

            }   else if ($category->name == 'repuestos de aires acondicionados') {
                
                $names = ['repuestos de aires acondicionados 1', 'repuestos de aires acondicionados 2'];

            }   else if ($category->name == 'repuestos de muchas cosas') {
                
                $names = ['repuestos de muchas cosas 1', 'repuestos de muchas cosas 2'];

            } 
            for ($i=0; $i < count($names); $i++) {
                $sub_category = SubCategory::create([
                    'name'          => $names[$i],
                    'category_id'   => $category->id,
                    'user_id'       => $user->id,
                ]);         
            }
        }
    }
}
 