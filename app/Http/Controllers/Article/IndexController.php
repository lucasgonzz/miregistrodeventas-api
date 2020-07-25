<?php

namespace App\Http\Controllers;

use App\Article;
use App\BarCode;
use App\Exports\ArticlesExport;
use App\Image;
use App\Imports\ArticlesImport;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use \Gumlet\ImageResize;

class ArticleController extends Controller
{

    function index() {
        $user = Auth()->user();
        if ($user->hasRole('commerce')) {
        	$articles = Article::where('user_id',$this->getArticleOwnerId())
                                ->orderBy('id', 'DESC')
                                ->with('marker')
                                ->with('images')
                                ->with('category')
                                ->with(['providers' => function($q) {
                                    $q->orderBy('cost', 'asc');
                                }])
                                ->paginate(10);
        } else {
            $articles = Article::where('user_id',$this->getArticleOwnerId())
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

    function createOffer(Request $request) {
        foreach ($request->articles as $article) {
            $_article = Article::find($article['id']);
            $_article->offer_price = $article['offer_price'];
            $_article->save();
        }
        // foreach ($request->articles_id as $article_id) {
        //     $article = Article::find($article_id);
        //     $article->offer_price = $article->price - round(($request->porcentage/100)*$article->price, 2);
        //     $article->save();
        // }
    }

    function update(Request $request, $id) {
        $article = Article::find($id);
        $updated_article = $request->article;
        if ($updated_article['new_bar_code'] != '') {
            $article->bar_code = $updated_article['new_bar_code'];
        }
        if (isset($updated_article['offer_price'])) {
            $article->offer_price = $updated_article['offer_price'];
        }
        if ($updated_article['category_id'] != 0) {
            $article->category_id = $updated_article['category_id'];
        }
        if ($article->price != $updated_article['price']) {
            $article->previus_price = $article->price;
        }
        if (!$updated_article['act_fecha']) {
            $article->timestamps = false;
        } 
        $article->name = ucwords($updated_article['name']);
        $article->cost = $updated_article['cost'];
        $article->price = $updated_article['price'];
        $article->online_price = $updated_article['online_price'];
        if (!is_null($article->stock)) {
            if (!$updated_article['stock_null']) {
                $article->stock += (float)$updated_article['new_stock'];
            } else {
                $article->stock = null;
            }
        }
        $article->save();
                // return $updated_article;
        if (Auth()->user()->hasRole('commerce')) {
            $article->providers()
                            ->attach(
                                $updated_article['provider'], 
                                [
                                    'amount' => $updated_article['new_stock'] != 0 ? (float)$updated_article['new_stock'] : null,
                                    'cost' => $updated_article['cost'],
                                    'price' => $updated_article['price'],
                                ]);
        }
        return $article;
    }

    function updateCategory(Request $request) {
        foreach ($request->articles_id as $id) {
            $article = Article::find($id);
            $article->category_id = $request->category_id;
            $article->save();
        }
    }

    function updateImage(Request $request, $article_id) {
        $time = time();
        // $extension = $request->file->getClientOriginalExtension();
        $generated_new_name = $time . '.jpg';
        $upload_path = 'articles/'.$this->getArticleOwnerId();
        $request->file->storeAs($upload_path, $generated_new_name);
        
        Image::create([
            'article_id' => $article_id,
            'url'        => $generated_new_name,
        ]);
    }

    function deleteImage($image_id) {
        $image = Image::find($image_id);
        $path = 'articles/'.$this->getArticleOwnerId().'/'.$image->url;
        Storage::delete($path);
        $image->delete();
    }

    // Eliminar el archivo tambien aca arriba

    function setFirstImage($image_id) {
        $image = Image::find($image_id);
        $article = Article::find($image->article_id);
        // $user = User::find($this->getArticleOwnerId());
        $images = Image::where('article_id', $article->id)
                            ->get();
        $path = 'articles/'.$this->getArticleOwnerId().'/';
        $updated = false;
        foreach ($images as $image_) {
            if ($image_->url{0} == 'F') {
                $new_url = substr($image_->url, 1);
                // Renombrar la que empieza con F por la misma sin F
                Storage::disk('public')->move($path.$image_->url, $path.$new_url);
                // Se le cambia el nombre del archivo de la imagen que llega
                Storage::disk('public')->move($path.$image->url, $path.'F'.$image->url);
                $image_->url = $new_url;
                $image_->save();
                $image->url = 'F'.$image->url;
                $image->save();
                $updated = true;
            }
        }
        if (!$updated) {
            Storage::disk('public')->move($path.$image->url, $path.'F'.$image->url);
            $image->url = 'F'.$image->url;
            $image->save();
        }
    }

    function updateImages(Request $request) {
        // return $request->articles_id;
        $upload_path = 'articles/'.$this->getArticleOwnerId();
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
        }
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
        // $request->validate([
        //     'file' => 'required|file|image|size:1024|dimensions:max_width=500,max_height=500',
        // ]);
        // return $request->file;

        $errores = false;
        $user = Auth()->user();
        $article = new Article();
        if ($request->uncontable == 1) {
            $article->uncontable = 1;
            $article->measurement = $request->measurement;
        }
        if ($request->hasFile('file')) {
            $time = time();
            // $extension = $request->file->getClientOriginalExtension();
            // $generated_new_name = $time . '.' . $extension;
            $generated_new_name = $time . '.jpg';
            $upload_path = 'articles/'.$this->getArticleOwnerId();
            $request->file->storeAs($upload_path, $generated_new_name);
            $article->image = $generated_new_name;
        } 
        $article->bar_code = $request->bar_code;
        if ($request->online) {
            $article->online = 1;
            if ($request->online_price == '') {
                $article->online_price = $request->price;
            } else {
                $article->online_price = $request->online_price;
            }
        }
        if ($request->category_id != 0) {
            $article->category_id = $request->category_id;
        }
        $article->name = ucwords($request->name);
        $article->cost = $request->cost;
        $article->price = $request->price;
        $article->previus_price = 0;
        if ($request->stock != 0) {
            $article->stock = $request->stock;
        }
        $article->user_id = $this->getArticleOwnerId();

        $date = date('Y-m-d');

        // Revisar este codigo de la fecha
        if ($request->created_at != $date) {
            $article->created_at = $request->created_at;
            $article->updated_at = $request->created_at;
        }
        $article->save();
        if ($user->hasRole('commerce')) {
            $article->providers()->attach($request->provider, [
                                            'amount' => $request->stock,
                                            'cost' => $request->cost,
                                            'price' => $request->price
                                        ]);
        }

        // Se fija si hay un codigo de barras creado con el codigo que llega
        // si hay uno se setea el article_id del bar_code
        $bar_code = BarCode::where('user_id', $this->getArticleOwnerId())
                                ->where('name', $request->bar_code)
                                ->first();
        if ($bar_code === null) {
            // return 'No existe';
        } else {
            $bar_code->article_id = $article->id;
            $bar_code->save();
            // return 'ASD';
        }
        return $article;
    }


    function destroy($id) {
        $article = Article::find($id);
        if ($article->marker) {
            $article->marker->delete();
        }
        $article->delete();
    }

    function destroyArticles($ids) {
        foreach (explode('-', $ids) as $article_id) {
            $article = Article::find($article_id);
            if ($article->marker) {
                $article->marker->delete();
            }
            $article->delete();
        }
    }

    function deleteOffer($id) {
        $article = Article::find($id);
        $article->offer_price = null;
        $article->save();
    }

    function filter(Request $request) {
        $user = Auth()->user();
        $mostrar = $request->mostrar;
        $ordenar = $request->ordenar;
        $precio_entre = $request->precio_entre;
        $precio_minimo = (float)$request->precio_entre['min'];
        $precio_maximo = (float)$request->precio_entre['max'];
        $fecha_minimo = $request->fecha_entre['min'];
        $fecha_maximo = $request->fecha_entre['max'];

        $articles = Article::where('user_id', $this->getArticleOwnerId());

        // Mostrar
        if ($mostrar == 'oferta') {
            $articles = $articles->whereNotNull('offer_price');
        }
        else if ($mostrar == 'marker') {
                $fecha_actual = date('d-m-Y');
                $hace_6_meses = date('d-m-Y', strtotime($fecha_actual."- 6 month"));
                $articles = $articles->where('marker', 1);
        }
        else if ($mostrar == 'desactualizados') {
                $fecha_actual = date('d-m-Y');
                $hace_6_meses = date('d-m-Y', strtotime($fecha_actual."- 6 month"));
                $articles = $articles->whereDate('updated_at', '<=', $hace_6_meses);
        }
        else if ($mostrar == 'no-vendidos') {
            $articles = $articles->doesntHave('sales');
        }
        else if ($mostrar == 'no-stock') {
            $articles = $articles->where('stock', 0);
        }

        if ($user->hasRole('commerce')) {
            $providers = $request->providers;
            if (count($providers) == 1) {
                $articles = $articles->with('providers')->whereHas('providers', function(Builder $query) use ($providers) {
                    $query->where('name', $providers[0]);
                });
            } else {
                $articles = $articles->with('providers')->whereHas('providers', function(Builder $query) use ($providers) {
                    $query->whereIn('name', $providers);
                });
            }
        }

        // Ordenar
        if ($ordenar == 'nuevos-viejos') {
            $articles = $articles->orderBy('created_at', 'DESC');
        }
        if ($ordenar == 'viejos-nuevos') {
            $articles = $articles->orderBy('created_at', 'ASC');
        }
        if ($ordenar == 'caros-baratos') {
            $articles = $articles->orderBy('price', 'DESC');
        }
        if ($ordenar == 'baratos-caros') {
            $articles = $articles->orderBy('price', 'ASC');
        }

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

        return $articles->get();
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
