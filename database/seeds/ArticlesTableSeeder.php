<?php

use App\Article;
use App\Image;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = ['lomo', 'costillas', 'matambre', 'chorizo', 'pollo', 'pechuga', 'patas de muslo', 'peceto', 'pulpa comun', 'pulpa especial', 'puchero', 'vacio', 'asado', 'azotillo', 'bola de lomo', 'riñon', 'tripa gorda', 'molleja', 'chinchulin', 'lengua', 'pollo'];

        $names = ['campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada','campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada',];
        $categories_id = [1,1,1,1,1,1,6,3,2,2,2,4,5,6,7,1,1,3,3,3,8,8,5,1,1,1,1,1,1,1,1,6,3,2,2,2,4,5,6,7,1,1,3,3,3,8,8,5,1,1];
        $costs = [2000,2000,2000,2000,2000,2000,150,5000,500,500,500,300,1200,450,800,1000,1100,800,700,500,3500,3200,1000,540,300,2000,2000,2000,2000,2000,2000,150,5000,500,500,500,300,1200,450,800,1000,1100,800,700,500,3500,3200,1000,540,300,];
        Article::create([
            'bar_code'     => 123,
            'name'         => 'Hola',
            'cost'         => 500,
            'price'        => 1000,
            'stock'        => null,
            'user_id'      => 1,
            'category_id'  => null,
            'created_at'   => Carbon::now()->subDays(1),
            'featured' => null,
        ]);
        for ($user_id=1; $user_id < 3; $user_id++) { 
            for ($i=0;  $i < count($names); $i++) { 
                $cost = rand(50, 3000);
                $bar_code = rand(1000000000000, 9999999999999);
                $article = Article::create([
                    'bar_code'     => $bar_code,
                    'name'         => ucwords($names[$i]).' '.$i,
                    'cost'         => $costs[$i],
                    'price'        => $costs[$i] * 1.35,
                    'stock'        => 15,
                    'user_id'      => $user_id,
                    'category_id'  => $categories_id[$i],
                    'created_at'   => Carbon::now()->subDays($i),
                    'featured' => $i < 6 ? $i : null
                ]);
                if ($user_id == 2) {
                    $providers = Provider::where('user_id', $user_id)->get();
                    foreach ($providers as $provider) {
                        $article->providers()->attach($provider->id, [
                                                        'cost' => $article->cost,
                                                        'price' => $article->price,
                                                        'amount' => 15,
                                                    ]);
                    }
                }
                if ($user_id == 3) {
                    for ($j=0; $j < 2 ; $j++) {
                        if ($i + 1 < count($names)) {
                            $index = $i + $j;
                        } else {
                            $index = $i - $j;
                        }
                        Image::create([
                            'article_id' => $article->id,
                            'url'        => str_replace(' ', '-', $names[$index]).'.jpg',
                        ]);
                    }
                }
            }
        }
        Article::create([
            'bar_code'     => 111,
            'name'         => 'Hola2',
            'cost'         => 500,
            'price'        => 1000,
            'stock'        => null,
            'user_id'      => 1,
            'category_id'  => null,
            'created_at'   => Carbon::now()->subDays(1),
            'featured' => null,
        ]);
        Article::create([
            'bar_code'     => 222,
            'name'         => 'Hola3',
            'cost'         => 500,
            'price'        => 1000,
            'stock'        => null,
            'user_id'      => 1,
            'category_id'  => null,
            'created_at'   => Carbon::now()->subDays(1),
            'featured' => null,
        ]);
    }
}
