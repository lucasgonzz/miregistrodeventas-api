<?php

namespace App\Http\Controllers;

use App\Article;
use App\BarCode;
use App\Exports\ArticlesExport;
use App\Image;
use App\Imports\ArticlesImport;
use App\User;
use App\SpecialPrice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use \Gumlet\ImageResize;

class ArticleController extends Controller
{

    function index() {
        $user = Auth()->user();
        if ($user->hasRole('commerce')) {
            $articles = Article::where('user_id',$this->userId())
                                ->where('status', 'active')
                                ->orderBy('created_at', 'DESC')
                                ->with('images')
                                ->with('category')
                                ->with('specialPrices')
                                ->with(['providers' => function($q) {
                                    $q->orderBy('cost', 'asc');
                                }])
                                ->get();
        } else {
            $articles = Article::where('user_id',$this->userId())
                                ->orderBy('id', 'DESC')
                                ->with('images')
                                ->with('category')
                                ->get();
        }
        return response()->json(['articles' => $articles], 200);
    }

    function paginated() {
        $user = Auth()->user();
        if ($user->hasRole('commerce')) {
        	$articles = Article::where('user_id',$this->userId())
                                ->orderBy('id', 'DESC')
                                ->with('marker')
                                ->with('images')
                                ->with('category')
                                ->with('specialPrices')
                                ->with(['providers' => function($q) {
                                    $q->orderBy('cost', 'asc');
                                }])
                                ->paginate(10);
        } else {
            $articles = Article::where('user_id',$this->userId())
                                ->orderBy('id', 'DESC')
                                ->with('marker')
                                ->with('images')
                                ->with('category')
                                ->paginate(10);
        }
    	return [
                'pagination' => [
                    'total' => $articles->total(),
                    'current_page' => $articles->currentPage(),
                    'per_page' => $articles->perPage(),
                    'last_page' => $articles->lastPage(),
                    'from' => $articles->firstItem(),
                    'to' => $articles->lastPage(),
                ],
                'articles' => $articles 
            ];
    }

    function mostView($weeks_ago) {
        $articles = Article::where('user_id', $this->userId())
                            ->with(['views' => function($q) use($weeks_ago) {
                                $q->where('created_at', '>', Carbon::now()->subWeeks($weeks_ago));
                            }])
                            ->with('images')
                            ->take(50)
                            ->get();
        return response()->json(['articles' => $articles], 200);
    }

    function show($id) {
        $article = Article::where('id', $id)
                            ->with('category')
                            ->with('specialPrices')
                            ->with('images');
        if (Auth::user()->hasRole('commerce')) {
            $article = $article->with('providers');
        }
        return $article->first();
    }

    function update(Request $request) {
        $article = Article::find($request->id);
        $article->bar_code = $request->bar_code;
        $article->category_id = $request->category_id != 0 ? $request->category_id : null;
        if ($article->price != $request->price) {
            $article->previus_price = $article->price;
        }
        if (!$request->act_fecha) {
            $article->timestamps = false;
        } 
        $article->name = ucwords($request->name);
        $article->cost = $request->cost;
        $article->price = $request->price;
        if (!is_null($article->stock)) {
            if (!$request->stock_null) {
                $article->stock = $request->stock;
                $article->stock += $request->new_stock;
            } else {
                $article->stock = null;
            }
        }
        $article->save();
        if ($request->new_stock != 0) {
            $article->providers()
                            ->attach(
                                $request->provider_id, 
                                [
                                    'amount' => (float)$request->new_stock,
                                    'cost' => $request->cost,
                                    'price' => $request->price,
                                ]);
        }
        $special_prices = SpecialPrice::where('user_id', $this->userId())->get();
        if ($special_prices) {
            $article->specialPrices()->sync([]);
            foreach ($special_prices as $special_price) {
                if ($request->{$special_price->name} != '') {
                    $article->specialPrices()
                    ->attach(
                        $special_price->id, 
                        ['price' => (double)$request->{$special_price->name}]
                    );
                }
            }
        }
        $article = Article::where('id', $article->id)
                            ->with('images')
                            ->with('category')
                            ->with('specialPrices')
                            ->with(['providers' => function($q) {
                                $q->orderBy('cost', 'asc');
                            }])
                            ->first();
        return response()->json(['article' => $article], 200);
    }

    function updateCategory(Request $request) {
        foreach ($request->articles_id as $id) {
            $article = Article::find($id);
            $article->category_id = $request->category_id;
            $article->save();
        }
    }

    function addImage(Request $request, $id) {
        $image = Image::create([
            'article_id' => $id,
            'url'        => $request->path,
        ]);
        $article = Article::where('id', $id)
                            ->with('images')
                            ->with('category')
                            ->with('specialPrices')
                            ->with(['providers' => function($q) {
                                $q->orderBy('cost', 'asc');
                            }])
                            ->first();
        return response()->json(['article' => $article], 201);
    }

    function updateImage(Request $request, $article_id) {
        if ($request->hasFile('file')) {
            $name = time().$this->userId().'.jpg';
            $path = $request->file('file')->storeAs('public/articles/'.$this->userId(), $name);
            Image::create([
                'article_id' => $article_id,
                'url'        => explode('/', $path)[count(explode('/', $path))-1],
            ]);
        }
    }

    function deleteImage($image_id) {
        $image = Image::find($image_id);
        $path = 'articles/'.$this->userId().'/'.$image->url;
        Storage::delete($path);
        $image->delete();
    }

    // Eliminar el archivo tambien aca arriba

    function setFirstImage($image_id) {
        $image = Image::find($image_id);
        $article = Article::find($image->article_id);
        $images = Image::where('article_id', $article->id)
                            ->get();
        foreach ($images as $image_) {
            if ($image_->first) {
                $image_->first = 0;
                $image_->save();
            }
        }
        $image->first = 1;
        $image->save();
        $article = Article::where('id', $article->id)
                            ->with('images')
                            ->with('category')
                            ->with('specialPrices')
                            ->with(['providers' => function($q) {
                                $q->orderBy('cost', 'asc');
                            }])
                            ->first();
        return response()->json(['article' => $article], 200);
    }

    function updateImages(Request $request) {
        // return $request->articles_id;
        $upload_path = 'articles/'.$this->userId();
        $articles_id = explode(',', $request->articles_id);
        $articles = [];
        for ($i=count($articles_id) - 1; $i >= 0; $i--) { 
            $time = time().$i;
            $name = 'file'.(count($articles_id) - 1 - $i);
            // $extension = $request->$name->getClientOriginalExtension();
            $generated_new_name = $time . '.jpg';
            $request->$name->storeAs($upload_path, $generated_new_name);
            $article = Article::find($articles_id[$i]);
            if (!is_null($article->image)) {
                $last_image = $article->image;
                unlink($upload_path . '/' . $article->image);
            }
            $article->image = $generated_new_name;
            $article->save();
            $articles[] = $article;
        }
        return $articles;
    }

    function moveImages(Request $request) {
        $index = 0;
        foreach ($request->articles_actual as $article_id) {
            $article = Article::find($article_id);
            $article->image = $request->images_original[$index];
            $article->save();
            $index++;
        }
    }

    function updateByPorcentage(Request $request) {
        $decimals = (bool)$request->decimals;
        $articles_ids = $request->articles_ids;
        $articles = [];
        foreach ($articles_ids as $article_id) {
            $article = Article::find($article_id);
            if (!empty($request->cost)) {
                if($decimals) {
                    $article->cost += round(($request->cost/100)*$article->cost, 2);
                } else {
                    $article->cost += round(($request->cost/100)*$article->cost, 0);
                }
            }
            if (!empty($request->price)) {
                $article->previus_price = $article->price;
                if($decimals) {
                    $article->price += round(($request->price/100)*$article->price, 2);
                } else {
                    $article->price += round(($request->price/100)*$article->price, 0);
                }
            }
            $article->save();
            $articles[] = $article;
        }
        return response()->json(['articles' => $articles], 200);
    }

    function setFeatured($article_id) {
        $article = Article::find($article_id);
        if (!is_null($article->featured)) {
            $article->featured = null;
        } else {
            $articles_featured = Article::where('user_id', $this->userId())
                                        ->whereNotNull('featured')
                                        ->get();
            $article->featured = count($articles_featured) + 1;
        }
        $article->save();
        $article = Article::where('id', $article->id)
                            ->with('images')
                            ->with('category')
                            ->with('specialPrices')
                            ->with(['providers' => function($q) {
                                $q->orderBy('cost', 'asc');
                            }])
                            ->first();
        return response()->json(['article' => $article], 200);
    }

    function setOnline($articles_id) {
        foreach (explode('-', $articles_id) as $id) {
            $article = Article::find($id);
            if (is_null($article->online_price)) {
                $article->online_price = $article->price;
            }
            $article->online = 1;
            $article->save();
        }
    }

    function removeOnline($articles_id) {
        foreach (explode('-', $articles_id) as $id) {
            $article = Article::find($id);
            $article->online = 0;
            $article->save();
        }
    }

    function store(Request $request) {
        $user = Auth()->user();
        $article = new Article();
        $article->bar_code = $request->bar_code;
        if ($request->category_id != 0) {
            $article->category_id = $request->category_id;
        }
        $article->name = ucfirst($request->name);
        $article->cost = $request->cost;
        $article->price = $request->price;
        if ($request->stock != '') {
            $article->stock = $request->stock;
        }
        $article->user_id = $user->id;
        $article->save();
        if ($user->hasRole('commerce')) {
            $article->providers()->attach($request->provider_id, [
                                            'amount' => $request->stock,
                                            'cost' => $request->cost,
                                            'price' => $request->price
                                        ]);
        }
        $special_prices = SpecialPrice::where('user_id', $user->id)->get();
        if ($special_prices) {
            foreach ($special_prices as $special_price) {
                if (isset($request->{$special_price->name})) {
                    $article->specialPrices()
                    ->attach(
                        $special_price->id, 
                        [
                            'price' => (double)$request->{$special_price->name}
                        ]
                    );
                }
            }
        }

        $article = Article::where('id', $article->id)
                            ->with('images')
                            ->with('category')
                            ->with('specialPrices')
                            ->with(['providers' => function($q) {
                                $q->orderBy('cost', 'asc');
                            }])
                            ->first();
        return response()->json(['article' => $article], 201);
    }

    function destroy($id) {
        $article = Article::find($id);
        $article->status = 'inactive';
        $article->save();
    }

    function delete($ids) {
        foreach (explode('-', $ids) as $article_id) {
            $article = Article::find($article_id);
            $article->status = 'inactive';
            $article->save();
        }
        return response(null, 200);
    }

    function deleteOffer($id) {
        $article = Article::find($id);
        $article->offer_price = null;
        $article->save();
    }

    function filter(Request $request) {
        $user = Auth()->user();
        $mostrar = $request->mostrar;
        $type = $request->type;
        // $ordenar = $request->ordenar;
        $precio_entre = $request->precio_entre;
        $precio_minimo = (float)$request->precio_entre['min'];
        $precio_maximo = (float)$request->precio_entre['max'];
        $fecha_minimo = $request->fecha_entre['min'];
        $fecha_maximo = $request->fecha_entre['max'];

        $articles = Article::where('user_id', $this->userId());

        // Ordenar
        // if ($ordenar == 'nuevos-viejos') {
        //     $articles = $articles->orderBy('created_at', 'DESC');
        // }
        // if ($ordenar == 'viejos-nuevos') {
        //     $articles = $articles->orderBy('created_at', 'ASC');
        // }
        // if ($ordenar == 'caros-baratos') {
        //     $articles = $articles->orderBy('price', 'DESC');
        // }
        // if ($ordenar == 'baratos-caros') {
        //     $articles = $articles->orderBy('price', 'ASC');
        // }

        // Type
        if ($type === 'markers') {
            $articles = $articles->whereHas('marker');
        } else if ($type === 'featured') {
            $articles = $articles->whereNotNull('featured');
        }

        if ($user->hasRole('commerce')) {
            $articles = $articles->with('providers');
            // $provider = $request->provider;
            // if ($provider != 0) {
            //     $articles = $articles->whereHas('providers', function(Builder $q) use ($provider) {
            //         $q->where('provider_id', $provider);
            //     });
            // }
        }

        // Categorias
        $category = $request->category;
        if ($category != 0) {
            $articles = $articles->where('category_id', $category);
        }

        // Proveedores
        // $provider = $request->provider;
        // if ($provider != 0) {
        //     $articles = $articles->whereHas('providers', function(Builder $q) use($provider) {
        //         $q->where('provider_id', $provider);
        //     });
        // }

        if ($precio_minimo != '' && $precio_maximo != '') {
            $articles = $articles->whereBetween('offer_price', 
                                                    [$precio_minimo, $precio_maximo]
                                                )->orWhereBetween('price', 
                                                    [$precio_minimo, $precio_maximo]
                                                );
        }

        if ($fecha_minimo != '' && $fecha_maximo != '') {
            $fecha_maximo = new Carbon($fecha_maximo);
            $fecha_maximo->addDay();
            $articles = $articles->whereBetween('created_at', 
                                                    [$fecha_minimo, $fecha_maximo]
                                                );
        }

        $articles->with('images');
        $articles = $articles->get();
        return response()->json(['articles' => $articles], 200);
    }

    function export() {
        return Excel::download(new ArticlesExport, 'miregistrodeventas-articulos.xlsx');
    }

    function import(Request $request) {
        (new ArticlesImport)->import($request->exel);
        // Excel::import(new ArticlesImport, $request->exel);
        if (Auth()->user()->hasRole('provider')) {
            return redirect()->route('listado.provider')->with('success', 'Importacion realizada con exito');
        } else {
            return redirect()->route('listado.commerce')->with('success', 'Importacion realizada con exito');
        }
    }
}
