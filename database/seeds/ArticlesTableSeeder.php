<?php

use App\Article;
use App\Description;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Image;
use App\Provider;
use App\SubCategory;
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


    public $images = [
            'v1628604855/articles/zsirpaost45zyjkclsbn.jpg', 
            'v1628344265/articles/ih96nbu0i6ahnta8nnxd.jpg', 
            'v1628723149/articles/rkqnanzvxakybqkatajc.jpg', 
            'v1628192113/articles/ezleh2tpnosmmx7chfw5.jpg',
            'v1627484681/articles/rjexmxcyndhxxt7lc3vo.jpg',
            'v1627310032/articles/ya9xayo6ze60mslhmt3u.jpg',
            'v1627420021/articles/eeucqaqmz4541s6immuv.jpg'
        ];

    public $iphone_images = [
            'v1630938818/articles/e8cm0xcpskcyjdby1zmd.jpg', 
            'v1630938881/articles/v6uuldxjofumcv9uqgc1.jpg', 
            'v1630940250/articles/agfts1knzv9fwwxhvwms.jpg',
            'v1630940210/articles/ygdg6bng12atpnzkr0a7.jpg',
            'v1630940478/articles/u4uyisxw3ylq5jkdmnbr.jpg', 
            'v1630940431/articles/qkhnteqfgpnfjkcrquqz.jpg',
        ];
    public function run()
    {

        $names = ['campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada','campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada',];
        $iphones = ['Iphone 7', 'Iphone 7 plus', 'Iphone 8', 'Iphone 8 plus', 'Iphone 9', 'Iphone 9 plus', 'Iphone 10', 'Iphone 10 plus', 'Iphone 11', 'Iphone 11 plus', 'Cargador comun', 'Cabezal', 'Cargador comun', 'Cabezal', 'Auricular Bluetooth', 'Auricular con cable', 'Auricular Bluetooth', 'Auricular con cable'];
       
        for ($user_id=1; $user_id < 4; $user_id++) { 
            if ($user_id < 3) {
                $limit = count($names);
            } else {
                $limit = count($iphones);
            }
            for ($i=0;  $i < $limit; $i++) { 
                $cost = rand(50, 3000);
                $name = $user_id < 3 ? $names[$i] : $iphones[$i];
                $bar_code = rand(1000000000000, 9999999999999);
                $article = Article::create([
                    'bar_code'     => $bar_code,
                    'name'         => $name,
                    'slug'         => ArticleHelper::slug($name),
                    'cost'         => 5,
                    'price'        => 10,
                    'stock'        => $i < 10 ? 0 : 14,
                    'user_id'      => $user_id,
                    'sub_category_id'  => $this->subcategoryId($user_id, $i),
                    'created_at'   => Carbon::now()->subDays($i),
                    'featured' => $i < 8 ? $i : null,
                ]);
                Description::create([
                    'title'      => 'Almacentamiento',
                    'content'    => 'Este modelo nos entrega una importante capacidad de almacenamiento loco mal esta re zarpada pero mal mal mal. Este modelo nos entrega una importante capacidad de almacenamiento loco mal esta re zarpada pero mal mal mal. Este modelo nos entrega una importante capacidad de almacenamiento loco mal esta re zarpada pero mal mal mal',
                    'article_id' => $article->id,
                ]);
                Description::create([
                    'title'      => 'Pantalla',
                    'content'    => 'Tiene una pantalla muy linda y bueno nada esta todo re bien viste mas que bien',
                    'article_id' => $article->id,
                ]);
                Description::create([
                    'title'      => 'Bateria',
                    'content'    => 'La bateria se la re aguanta mal mal mal La bateria se la re aguanta mal mal mal La bateria se la re aguanta mal mal mal ',
                    'article_id' => $article->id,
                ]);
                $article->colors()->attach([1,2,3,4,rand(5,12)]);
                // for ($i=0; $i < 2; $i++) { 
                //     $article->colors()->attach(rand(1, 12));
                // }
                for ($j=0; $j < 1; $j++) { 
                    Image::create([
                        'article_id' => $article->id,
                        'url'        => $this->imageUrl($user_id, $i, $j),
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
                if ($user_id < 3 && $i < 10) {
                    for ($j=0; $j < 7; $j++) { 
                        Variant::create([
                            'description' => 'Modelo '.$j,
                            'stock'       => 7,
                            'article_id'  => $article->id,
                            'url'         => $this->images[$j],
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
                'url'        => $this->images[0],
            ]);
        }
    }

    function subcategoryId($user_id, $i) {
        if ($user_id < 3) {
            return rand(1,40);
        } else {
            if ($i <= 10) {
                $sub_category = SubCategory::where('name', 'Iphones')->first();
                return $sub_category->id;
            }
            if ($i > 10 && $i <= 12) {
                $sub_category = SubCategory::where('name', 'Iphon')->first();
                return $sub_category->id;
            }
            if ($i > 12 && $i <= 14) {
                $sub_category = SubCategory::where('name', 'Android')->first();
                return $sub_category->id;
            }
            if ($i > 14 && $i <= 16) {
                $sub_category = SubCategory::where('name', 'Casco')->first();
                return $sub_category->id;
            }
            if ($i > 16 && $i <= 18) {
                $sub_category = SubCategory::where('name', 'Comunes')->first();
                return $sub_category->id;
            }
        }
    }

    function imageUrl($user_id, $i, $j) {
        if ($user_id < 3) {
            return $this->images[rand(0,6)];
        } else {
            if ($i <= 10) {
                return $this->iphone_images[$j];
            }
            if ($i == 11 || $i == 13) {
                return $this->iphone_images[2];
            }
            if ($i == 12 || $i == 14) {
                return $this->iphone_images[3];
            }
            if ($i == 15 || $i == 17) {
                return $this->iphone_images[4];
            }
            if ($i == 16 || $i == 18) {
                return $this->iphone_images[5];
            }
        }
    }
}
