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
            'iphone 7'              => 'v1630938818/articles/e8cm0xcpskcyjdby1zmd.jpg', 
            'iphone 7 plus'         => 'v1630938881/articles/v6uuldxjofumcv9uqgc1.jpg', 
            'iphone xr'             => 'v1633540148/articles/ulycltnzdx2plkcjbm1e.jpg',
            'iphone se'             => 'v1633540062/articles/jpdgffh05cvd91msekde.jpg',
            'iphone 11'             => 'v1633540438/articles/ebefqutk8fmotvnutpbi.jpg',
            'iphone 12'             => 'v1633539825/articles/i4zwmy46xduh1wbvsctw.jpg',
            'cargador'              => 'v1630940250/articles/agfts1knzv9fwwxhvwms.jpg',
            'cable'                 => 'v1630940210/articles/ygdg6bng12atpnzkr0a7.jpg',
            'auricular con cable'   => 'v1630940478/articles/u4uyisxw3ylq5jkdmnbr.jpg', 
            'auricular inalambrico' => 'v1630940431/articles/qkhnteqfgpnfjkcrquqz.jpg',
        ];
    public function run()
    {

        $names = ['campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada','campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada',];

        $iphones = [
            [
                'name'              => 'Iphone 7',
                'brand_id'          => 1,
                'cost'              => 45000,
                'price'             => 60000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'images'            => [
                    $this->iphone_images['iphone 7'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 7 Plus',
                'brand_id'          => 1,
                'cost'              => 55000,
                'price'             => 70000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'images'            => [
                    $this->iphone_images['iphone 7 plus'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone Xr',
                'brand_id'          => 1,
                'cost'              => 70000,
                'price'             => 90000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'featured'          => 1,
                'images'            => [
                    $this->iphone_images['iphone xr'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone Se',
                'brand_id'          => 1,
                'cost'              => 60000,
                'price'             => 85000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'images'            => [
                    $this->iphone_images['iphone se'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 11',
                'brand_id'          => 1,
                'cost'              => 90000,
                'price'             => 105000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'featured'          => 1,
                'images'            => [
                    $this->iphone_images['iphone 11'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 11 Pro',
                'brand_id'          => 1,
                'cost'              => 100000,
                'price'             => 110000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'images'            => [
                    $this->iphone_images['iphone 11'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 12',
                'brand_id'          => 1,
                'cost'              => 110000,
                'price'             => 130000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'featured'          => 1,
                'images'            => [
                    $this->iphone_images['iphone 12'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 12 Pro',
                'brand_id'          => 1,
                'cost'              => 120000,
                'price'             => 140000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 1,
                'images'            => [
                    $this->iphone_images['iphone 12'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Cargador con cable',
                'brand_id'           => 2,
                'cost'              => 1000,
                'price'             => 1500,
                'stock'             => rand(4,7),
                'sub_category_id'   => 2,
                'featured'          => 1,
                'images'            => [
                    $this->iphone_images['cable'],
                    $this->iphone_images['cargador'],
                ]
            ],
            [
                'name'              => 'Cargador cabezal',
                'brand_id'           => 2,
                'cost'              => 700,
                'price'             => 1000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 2,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'name'              => 'Cargador con cable',
                'brand_id'           => 2,
                'cost'              => 1000,
                'price'             => 1500,
                'stock'             => rand(4,7),
                'sub_category_id'   => 3,
                'featured'          => 1,
                'images'            => [
                    $this->iphone_images['cable'],
                    $this->iphone_images['cargador'],
                ]
            ],
            [
                'name'              => 'Cargador cabezal',
                'brand_id'           => 2,
                'cost'              => 700,
                'price'             => 1000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 3,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'name'              => 'Auricular Bluetooth',
                'brand_id'          => 2,
                'cost'              => 2000,
                'price'             => 3000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 4,
                'featured'          => 1,
                'images'            => [
                    $this->iphone_images['auricular inalambrico'],
                    $this->iphone_images['auricular con cable'],
                ]
            ],
            [
                'name'              => 'Auricular con cable',
                'brand_id'          => 2,
                'cost'              => 1900,
                'price'             => 2400,
                'stock'             => rand(4,7),
                'sub_category_id'   => 4,
                'images'            => [
                    $this->iphone_images['auricular con cable'],
                    $this->iphone_images['auricular inalambrico'],
                ]
            ],
        ];
       
        for ($user_id=2; $user_id <= 2; $user_id++) { 
            for ($h=1; $h < 4; $h++) { 
                foreach ($iphones as $iphone) {
                    $bar_code = rand(1000000000000, 9999999999999);
                    $article = Article::create([
                        'bar_code'          => $bar_code,
                        'name'              => $iphone['name'],
                        'brand_id'              => $iphone['brand_id'],
                        'slug'              => ArticleHelper::slug($iphone['name']),
                        'cost'              => $iphone['cost'],
                        'price'             => $iphone['price'],
                        'stock'             => $iphone['stock'],
                        'user_id'           => $user_id,
                        'sub_category_id'   => $iphone['sub_category_id'],
                        'featured'          => isset($iphone['featured']) ? $iphone['featured'] : null,
                    ]);
                    $this->createDescriptions($article);
                    $article->colors()->attach([1,2]);
                    $color_id = 1;
                    foreach ($iphone['images'] as $url) { 
                        Image::create([
                            'article_id' => $article->id,
                            'url'        => $url,
                            'color_id'   => $color_id,
                        ]);
                        $color_id++;
                    }
                    for ($j=0; $j < 4; $j++) {
                    }
                    $providers = Provider::where('user_id', $user_id)
                                            ->take(3)
                                            ->get();
                    foreach ($providers as $provider) {
                        $article->providers()->attach($provider->id, [
                                                        'cost' => $article->cost,
                                                        'price' => $article->price,
                                                        'amount' => rand(1,4),
                                                    ]);
                    }
                }
            }
        }
    }

    function createDescriptions($article) {
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
        } else {
            return $this->images[rand(0,6)];
        }
    }
}
