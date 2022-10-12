<?php

use App\Article;
use App\ArticleDiscount;
use App\Description;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Image;
use App\Provider;
use App\SubCategory;
use App\User;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        // $this->candy();

        // $this->kasAberturas();

        // $this->pinocho();

        // $this->nebulaStore();

        $this->colman();

        $this->articlesIva('colman');

        return;
        $names = ['campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada','campera grande', 'campera grande', 'pantalon azul grande con cosas', 'sombrero', 'campera boca azul', 'campera boca blanca', 'campera river roja', 'campera river roja', 'cargador usb', 'escritorio para pc', 'funda iphone bordo', 'funda iphone celeste', 'funda iphone xr roja', 'linterna', 'mochila topper', 'mouse con luz', 'peluche de unicornio', 'remera deportiva', 'remera running', 'silla de comedor', 'silla de madera', 'silla de plastico', 'zapatilla adidas', 'zapatilla fila', 'mochila floreada',];

        $iphones = [
            [
                'name'              => 'Iphone 7 pesos',
                'brand_id'          => 1,
                'cost'              => 45000,
                'price'             => 60000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'with_dolar'        => false,
                'images'            => [
                    $this->iphone_images['iphone 7'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 7 Plus',
                'brand_id'          => 1,
                'cost'              => 400,
                'price'             => 900,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'with_dolar'        => true,
                'images'            => [
                    $this->iphone_images['iphone 7 plus'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone Xr',
                'brand_id'          => 1,
                'cost'              => 500,
                'price'             => 1000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'with_dolar'        => true,
                'featured'          => 1,
                'images'            => [
                    $this->iphone_images['iphone xr'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone Se',
                'brand_id'          => 1,
                'cost'              => 1000,
                'price'             => 2000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'with_dolar'        => true,
                'images'            => [
                    $this->iphone_images['iphone se'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 11',
                'brand_id'          => 1,
                'cost'              => 2000,
                'price'             => 3000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'featured'          => 1,
                'with_dolar'        => true,
                'images'            => [
                    $this->iphone_images['iphone 11'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 11 Pro',
                'brand_id'          => 1,
                'cost'              => 1100,
                'price'             => 2100,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'with_dolar'        => true,
                'images'            => [
                    $this->iphone_images['iphone 11'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 12',
                'brand_id'          => 1,
                'cost'              => 2000,
                'price'             => 3000,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'featured'          => 1,
                'with_dolar'        => true,
                'images'            => [
                    $this->iphone_images['iphone 12'],
                    $this->iphone_images['iphone 7'],
                ]
            ],
            [
                'name'              => 'Iphone 12 Pro',
                'brand_id'          => 1,
                'cost'              => 2100,
                'price'             => 3100,
                'stock'             => rand(4,7),
                'sub_category_id'   => 6,
                'with_dolar'        => true,
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
                'sub_category_id'   => 7,
                'featured'          => 1,
                'with_dolar'        => false,
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
                'sub_category_id'   => 8,
                'with_dolar'        => false,
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
                'sub_category_id'   => 6,
                'featured'          => 1,
                'with_dolar'        => false,
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
                'sub_category_id'   => 6,
                'with_dolar'        => false,
                'images'            => [
                    $this->iphone_images['auricular con cable'],
                    $this->iphone_images['auricular inalambrico'],
                ]
            ],
        ];

        $articles_iva = [
            [
                'name' => 'Iva 27',
                'price' => 100,
                'iva_id' => 1,
            ],
            [
                'name' => 'Iva 21',
                'price' => 100,
                'iva_id' => 2,
            ],
            [
                'name' => 'Iva 10.5',
                'price' => 100,
                'iva_id' => 3,
            ],
            [
                'name' => 'Iva 5',
                'price' => 100,
                'iva_id' => 4,
            ],
            [
                'name' => 'Iva 2.5',
                'price' => 100,
                'iva_id' => 5,
            ],

            [
                'name' => 'Iva 0',
                'price' => 100,
                'iva_id' => 6,
            ],
            [
                'name' => 'Iva No Gravado',
                'price' => 100,
                'iva_id' => 7,
            ],
            [
                'name' => 'Iva Exento',
                'price' => 100,
                'iva_id' => 8,
            ],
        ];

        


        $fiushh = User::where('company_name', 'Fiushh')->first();
        foreach ($articles_iva as $article) {
            Article::create([
                'name' => $article['name'],
                'price' => $article['price'],
                'iva_id' => $article['iva_id'],
                'user_id' => $fiushh->id,
            ]);        
        }
        // return;
        $users = User::where('company_name', 'Fiushh')
                    ->orWhere('company_name', 'Pinocho')
                    ->get();
        foreach ($users as $user) {
            for ($vuelta=1; $vuelta < 4; $vuelta++) { 
                foreach ($iphones as $iphone) {
                    $bar_code = rand(1000000000000, 9999999999999);
                    $name = $iphone['name'].' mod.'.$vuelta;
                    $article = Article::create([
                        'bar_code'          => $bar_code,
                        'name'              => $name,
                        'brand_id'          => $iphone['brand_id'],
                        'slug'              => ArticleHelper::slug($name),
                        'cost'              => $iphone['cost'],
                        'price'             => $iphone['price'],
                        'stock'             => $iphone['stock'],
                        'with_dolar'        => $iphone['with_dolar'],
                        'user_id'           => $user->id,
                        'sub_category_id'   => $iphone['sub_category_id'],
                        'featured'          => isset($iphone['featured']) ? $iphone['featured'] : null,
                        'created_at'        => Carbon::now()->subDays($vuelta),
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
                    $providers = Provider::where('user_id', $user->id)
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

    function candy() {
        $articles = [
            [
                'name'              => 'Cafe con leche',
                'price'             => 100,
            ],
            [
                'name'              => 'Medialuna',
                'price'             => 80,
            ],
            [
                'name'              => 'Tostada',
                'price'             => 50,
            ],
        ];
        $candy = User::where('company_name', 'CandyGuay')->first();
        foreach ($articles as $article) {
            Article::create([
                'name' => $article['name'],
                'price' => $article['price'],
                'user_id' => $candy->id,
            ]);        
        }
    }

    function kasAberturas() {
        $kas_aberturas_articles = [
            [
                'bar_code'          => '123',
                'provider_code'     => 'p-123',
                'name'              => 'Visagra mediana con muchas cosas utiles por ejemplo el hecho de que la podes usar para ir a pescar con tus amigos',
                'stock'             => 10,
                'cost'              => 50,
                'percentage_gain'   => 50,
                'sub_category_name' => 'de exterior',
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '234',
                'provider_code'     => 'p-234',
                'name'              => 'Picaporte',
                'stock'             => 10,
                'cost'              => 200,
                'sub_category_name' => 'industriales',
                'percentage_gain'   => 30,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '345',
                'provider_code'     => 'p-345',
                'name'              => 'Cerradura reforzada',
                'stock'             => 10,
                'cost'              => 700,
                'sub_category_name' => 'de otras cosas',
                'percentage_gain'   => 50,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '456',
                'provider_code'     => 'p-456',
                'name'              => 'revestimineto',
                'stock'             => 10,
                'cost'              => 700,
                'percentage_gain'   => 50,
                'sub_category_name' => 'puertas',
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '567',
                'provider_code'     => 'p-567',
                'name'              => 'revestimineto',
                'stock'             => 10,
                'cost'              => 700,
                'percentage_gain'   => 50,
                'sub_category_name' => 'portones',
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '678',
                'provider_code'     => 'p-678',
                'name'              => 'Visagra mediana con muchas cosas utiles por ejemplo el hecho de que la podes usar para ir a pescar con tus amigos',
                'stock'             => 10,
                'cost'              => 50,
                'percentage_gain'   => 50,
                'sub_category_name' => 'nacionales',
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '789',
                'provider_code'     => 'p-789',
                'name'              => 'Visagra mediana con',
                'stock'             => 10,
                'cost'              => 50,
                'percentage_gain'   => 50,
                'sub_category_name' => 'importados',
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
        ];

        $ct = new Controller();

        $kas_aberturas = User::where('company_name', 'kas aberturas')->first();
        foreach ($kas_aberturas_articles as $article) {
            $art = Article::create([
                'num'               => $ct->num('articles', $kas_aberturas->id),
                'bar_code'          => $article['bar_code'],
                'provider_code'     => $article['provider_code'],
                'name'              => $article['name'],
                'slug'              => ArticleHelper::slug($article['name']),
                'cost'              => $article['cost'],
                'stock'             => $article['stock'],
                'stock_min'         => 1,
                'percentage_gain'   => $article['percentage_gain'],
                'sub_category_id'   => $this->getSubcategory($kas_aberturas, $article)->id,
                'user_id'           => $kas_aberturas->id,
            ]);    
            foreach ($article['images'] as $url) { 
                Image::create([
                    'article_id' => $art->id,
                    'url'        => $url,
                ]);
            }    
        }
    }

    function pinocho() {
        $articles = [
            [
                'bar_code'          => '123',
                'featured'          => 1,
                'name'              => 'Auricular casco',
                'featured'          => 1,
                'stock'             => 10,
                'cost'              => 50,
                'price'             => 50,
                'sub_category_id'   => 1,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '1233',
                'featured'          => 1,
                'name'              => 'Auricular comun',
                'featured'          => 2,
                'stock'             => 0,
                'cost'              => 50,
                'price'             => 50,
                'sub_category_id'   => 2,
                'stock'             => 0,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '234',
                'name'              => 'Inflable de personajes',
                'featured'          => null,
                'stock'             => 10,
                'cost'              => 200,
                'price'             => 30,
                'sub_category_id'   => 3,
                'stock'             => null,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '2345',
                'name'              => 'Inflable de hormigas',
                'featured'          => 3,
                'stock'             => 10,
                'cost'              => 200,
                'price'             => 30,
                'sub_category_id'   => 4,
                'stock'             => null,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
            [
                'bar_code'          => '2345',
                'name'              => 'sonajero infantil',
                'featured'          => null,
                'stock'             => null,
                'cost'              => 200,
                'price'             => 30,
                'sub_category_id'   => 5,
                'stock'             => null,
                'images'            => [
                    $this->iphone_images['cargador'],
                    $this->iphone_images['cable'],
                ]
            ],
        ];

        $user = User::where('company_name', 'pinocho')->first();
        for ($i=1; $i < 10; $i++) { 
            foreach ($articles as $article) {
                $art = Article::create([
                    'bar_code'          => $article['bar_code'],
                    'featured'          => $article['featured'],
                    'name'              => $article['name'].' $i: '.$i,
                    'slug'              => ArticleHelper::slug($article['name']),
                    'cost'              => $article['cost'],
                    'stock'             => $article['stock'] ,
                    'price'             => $article['price'],
                    'sub_category_id'   => $article['sub_category_id'],
                    'stock'             => $article['stock'],
                    'user_id'           => $user->id,
                ]);    
                foreach ($article['images'] as $url) { 
                    Image::create([
                        'article_id' => $art->id,
                        'url'        => $url,
                    ]);
                }
                $this->createDiscount($art);    
            }
        }
    }

    function nebulaStore() {
        $articles = [
            [
                'bar_code'          => '',
                'name'              => 'Campera Tommy',
                'stock'             => 2,
                'cost'              => 500,
                'price'             => 700,
                'sub_category_name' => 'Inflables',
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [],
                'sizes'             => [],
            ],
            [
                'bar_code'          => '',
                'name'              => 'Campera de lana',
                'stock'             => 10,
                'cost'              => 200,
                'price'             => 800,
                'sub_category_name' => 'De lana',
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [1, 2],
                'sizes'             => [2, 3],
            ],
            [
                'bar_code'          => '',
                'name'              => 'Jean',
                'stock'             => 10,
                'cost'              => 700,
                'price'             => 800,
                'sub_category_name' => 'Jeans',
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [1],
                'sizes'             => [],
            ],
            [
                'bar_code'          => '',
                'name'              => 'Jean',
                'stock'             => 10,
                'cost'              => 700,
                'price'             => 800,
                'sub_category_name' => 'De cuero',
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [],
                'sizes'             => [1],
            ],
            [
                'bar_code'          => '',
                'name'              => 'Zapatilla Adidas',
                'stock'             => 10,
                'cost'              => 700,
                'price'             => 800,
                'sub_category_name' => 'Deportivas',
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [1],
                'sizes'             => [],
            ],
            [
                'bar_code'          => '',
                'name'              => 'Zapatilla Vanz',
                'stock'             => 10,
                'cost'              => 700,
                'price'             => 800,
                'sub_category_name' => 'Urbanas',
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [],
                'sizes'             => [1],
            ],
        ];

        $user = User::where('company_name', 'nebulaStore')->first();
        foreach ($articles as $article) {
            $art = Article::create([
                'bar_code'          => $article['bar_code'],
                'name'              => $article['name'],
                'slug'              => ArticleHelper::slug($article['name']),
                'cost'              => $article['cost'],
                'stock'             => $article['stock'] ,
                'price'             => $article['price'],
                'sub_category_id'   => $this->getSubcategory($user, $article)->id,
                'user_id'           => $user->id,
            ]);    
            foreach ($article['images'] as $url) { 
                Image::create([
                    'article_id' => $art->id,
                    'url'        => $url,
                    'color_id'   => $this->getColorId($article),
                ]);
            }    
            foreach ($article['colors'] as $color) { 
                $art->colors()->attach($color);
            }    
            foreach ($article['sizes'] as $size) { 
                $art->sizes()->attach($size);
            }   
            $this->createDescriptions($art); 
        }
    }

    function articlesIva($company_name) {
        $user = User::where('company_name', $company_name)
                    ->first();
        $num = 0;
        $ct = new Controller();
        $articles_iva = [
            [
                'name' => 'Iva 27',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', '27', false, 'id'),
            ],
            [
                'name' => 'Iva 21',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', '21', false, 'id'),
            ],
            [
                'name' => 'Iva 10.5',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', '10.5', false, 'id'),
            ],
            [
                'name' => 'Iva 5',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', '5', false, 'id'),
            ],
            [
                'name' => 'Iva 2.5',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', '2.5', false, 'id'),
            ],

            [
                'name' => 'Iva 0',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', '0', false, 'id'),
            ],
            [
                'name' => 'Iva No Gravado',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', 'No Gravado', false, 'id'),
            ],
            [
                'name' => 'Iva Exento',
                'price' => 100,
                'iva_id' => $ct->getModelBy('ivas', 'percentage', 'Exento', false, 'id'),
            ],
        ];
        foreach ($articles_iva as $article) {
            $num++;
            $art = Article::create([
                'num'               => $num,
                'name'              => $article['name'],
                'slug'              => ArticleHelper::slug($article['name']),
                'cost'              => 50,
                'price'             => $article['price'],
                'iva_id'            => $article['iva_id'],
                'user_id'           => $user->id,
            ]); 
        }
    }

    function colman() {
        $user = User::where('company_name', 'colman')
                    ->first();
        $bsas = Provider::where('user_id', $user->id)
                            ->where('name', 'Buenos Aires')
                            ->first();
        $rosario = Provider::where('user_id', $user->id)
                            ->where('name', 'Rosario')
                            ->first();
        $articles = [
            [
                'num'               => '1',
                'bar_code'          => '123',
                'name'              => 'Plaqueta de BSAS',
                'stock'             => 10,
                'cost'              => 100,
                'price'             => null,
                'sub_category_name' => 'lavarropa nuevo',
                'provider_id'       => $bsas->id,
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [],
                'sizes'             => [],
            ],
            [
                'num'               => '2',
                'bar_code'          => '234',
                'name'              => 'Plaqueta de Rosario',
                'stock'             => 10,
                'cost'              => 100,
                'price'             => null,
                'sub_category_name' => 'lavarropa nuevo',
                'provider_id'       => $rosario->id,
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [],
                'sizes'             => [],
            ],
            [
                'num'               => '3',
                'bar_code'          => '345',
                'name'              => 'Aire de BSAS',
                'stock'             => 10,
                'cost'              => 200,
                'price'             => null,
                'sub_category_name' => 'aire nuevo',
                'provider_id'       => $bsas->id,
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [],
                'sizes'             => [],
            ],
            [
                'num'               => '4',
                'bar_code'          => '456',
                'name'              => 'Aire de Rosario',
                'stock'             => 10,
                'cost'              => 200,
                'price'             => null,
                'sub_category_name' => 'aire nuevo',
                'provider_id'       => $rosario->id,
                'images'            => [
                    $this->iphone_images['cargador'],
                    'v1661975918/articles/nolwz6b1otbjdrynafib.jpg',
                ],
                'colors'            => [],
                'sizes'             => [],
            ],
        ];
        foreach ($articles as $article) {
            $art = Article::create([
                'num'               => $article['num'],
                'bar_code'          => $article['bar_code'],
                'provider_code'     => 'p-'.$article['bar_code'],
                'name'              => $article['name'],
                'slug'              => ArticleHelper::slug($article['name']),
                'cost'              => $article['cost'],
                'stock'             => $article['stock'] ,
                'price'             => $article['price'],
                'sub_category_id'   => $this->getSubcategory($user, $article)->id,
                'user_id'           => $user->id,
            ]);    
            foreach ($article['images'] as $url) { 
                Image::create([
                    'article_id' => $art->id,
                    'url'        => $url,
                    'color_id'   => $this->getColorId($article),
                ]);
            }    
            $art->providers()->attach($article['provider_id'], [
                                        'cost'  => $article['cost'],
                                        'amount' => $article['stock'],
                                    ]);
            $this->createDescriptions($art); 
        }
    }

    function createDiscount($article) {
        ArticleDiscount::create([
            'percentage' => '10',
            'article_id' => $article->id,
        ]);
        ArticleDiscount::create([
            'percentage' => '20',
            'article_id' => $article->id,
        ]);
    }

    function getSubcategory($user, $article) {
        $sub_category = SubCategory::where('user_id', $user->id)
                                    ->where('name', $article['sub_category_name'])
                                    ->first();
        return $sub_category;
    }

    function getColorId($article) {
        if (count($article['colors']) >= 1) {
            return $article['colors'][0];
        }
        return null;
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
