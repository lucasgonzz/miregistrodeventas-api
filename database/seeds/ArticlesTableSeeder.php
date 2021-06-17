<?php

use App\Article;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Image;
use App\Provider;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $names = ['campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada','campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada',];
       
        for ($user_id=1; $user_id < 3; $user_id++) { 
            for ($i=0;  $i < 40; $i++) { 
                $cost = rand(50, 3000);
                $name = $names[$i];
                $bar_code = rand(1000000000000, 9999999999999);
                $article = Article::create([
                    'bar_code'     => $bar_code,
                    'name'         => $name,
                    'slug'         => ArticleHelper::slug($name),
                    'cost'         => 5,
                    'price'        => 10,
                    'stock'        => 14,
                    'user_id'      => $user_id,
                    'sub_category_id'  => rand(1,40),
                    'created_at'   => Carbon::now()->subDays($i),
                    'featured' => $i < 8 ? $i : null
                ]);
                $images = [
                    'v1616079010/articles/ztaa7kyj1cfqoj8fmsjp.jpg', 
                    'v1616538802/articles/kboz26romcgmiswoocjw.jpg', 
                    'v1615988247/articles/cumc9e2hifffpr498nz6.jpg', 
                    'v1615989993/articles/ofzfakuwnre6qy6plzw0.jpg',
                    'v1615988968/articles/b6gcidfseqa3f59zyjr1.jpg',
                    'v1616507853/articles/guuyxregqgje3nhmmefj.jpg',
                    'v1616506825/articles/kcjbnqtkphlyacfc3bks.jpg'
                ];
                for ($j=0; $j < 2; $j++) { 
                    Image::create([
                        'article_id' => $article->id,
                        'url'        => $images[$j],
                    ]);
                }
                $providers = Provider::where('user_id', $user_id)->get();
                foreach ($providers as $provider) {
                    $article->providers()->attach($provider->id, [
                                                    'cost' => $article->cost,
                                                    'price' => $article->price,
                                                    'amount' => 15,
                                                ]);
                }
                if ($i < 10) {
                    for ($j=0; $j < 7; $j++) { 
                        Variant::create([
                            'description' => 'Modelo '.$j,
                            'stock'       => 7,
                            'article_id'  => $article->id,
                            'url'         => $images[$j],
                        ]);
                    }
                }
            }
        }
        for ($i=1; $i < 4; $i++) { 
            $article = Article::create([
                'bar_code'     => 111,
                'name'         => 'Hola '.$i,
                'slug'         => ArticleHelper::slug('Hola '.$i),
                'cost'         => 500,
                'price'        => 1000,
                'stock'        => null,
                'user_id'      => 2,
                'sub_category_id'  => null,
                'created_at'   => Carbon::now()->subDays(1),
                'featured' => null,
            ]);
            Image::create([
                'article_id' => $article->id,
                'url'        => $images[0],
            ]);
        }
    }
}
