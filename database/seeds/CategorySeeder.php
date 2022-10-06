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

        $this->nebulaStore();

        $this->kasAberturas();

        $this->colman();
    }

    function pinocho() {
        $user = User::where('company_name', 'pinocho')->first();
        $categories = ['Auriculares con muchas cosas', 'INFLABLES', 'Juguetes infantiles'];
        foreach ($categories as $category) {
            Category::create([
                'name'    => $category,
                'user_id' => $user->id,
            ]);
        }
    }

    function nebulaStore() {
        $user = User::where('company_name', 'nebulaStore')->first();
        $categories = ['Camperas', 'Pantalones', 'Zapatillas', 'Bufandas', 'Remeras', 'Camisas'];
        foreach ($categories as $category) {
            Category::create([
                'name'    => $category,
                'user_id' => $user->id,
            ]);
        }
    }

    function kasAberturas() {
        $user = User::where('company_name', 'kas aberturas')->first();
        $categories = ['puertas', 'repuestos', 'visagras', 'calefactores'];
        foreach ($categories as $category) {
            Category::create([
                'name'    => $category,
                'user_id' => $user->id,
            ]);
        }
    }

    function colman() {
        $user = User::where('company_name', 'colman')->first();
        $categories = ['Lava ropas', 'Aires acondicionados'];
        foreach ($categories as $category) {
            Category::create([
                'name'    => $category,
                'user_id' => $user->id,
            ]);
        }
    }
}
