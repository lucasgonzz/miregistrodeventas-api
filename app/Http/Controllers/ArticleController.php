<?php

namespace App\Http\Controllers;

use App\Article;
use App\BarCode;
use App\Description;
use App\Exports\ArticlesExport;
use App\Http\Controllers\Helpers\ArticleFilterHelper;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\GeneralHelper;
use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\Pdf\ArticleTicketPdf;
use App\Http\Controllers\Helpers\UserHelper;
use App\Image;
use App\Imports\ArticlesImport;
use App\Notifications\CreatedArticle;
use App\Notifications\UpdatedArticle;
use App\SpecialPrice;
use App\User;
use App\Variant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use \Gumlet\ImageResize;

class ArticleController extends Controller
{

    function index($status = 'active') {
        $user = Auth()->user();
        $articles = Article::where('user_id',$this->userId())
                            ->where('status', $status)
                            ->orderBy('created_at', 'DESC')
                            ->withAll()
                            ->get();
        $articles = ArticleHelper::setPrices($articles);
        return response()->json(['models' => $articles], 200);
    }

    function mostViewed($weeks_ago) {
        $articles = Article::where('user_id', $this->userId())
                            ->with(['views' => function($q) use($weeks_ago) {
                                $q->where('created_at', '>', Carbon::now()->subWeeks($weeks_ago));
                            }])
                            ->with('images.color')
                            ->with('sizes')
                            ->with('colors')
                            ->with('condition')
                            ->with('views.buyer')
                            ->take(50)
                            ->withCount('views')
                            ->get()
                            ->where('views_count', '>=', 1)
                            ->sortBy(function($q) {
                                return $q->views_count;
                            });
        return response()->json(['models' => $articles], 200);
    }

    function show($id) {
        $article = ArticleHelper::getFullArticle($id);
        return response()->json(['model' => $article], 200);
    }

    function update(Request $request) {
        $article = Article::find($request->id);
        if (!UserHelper::getFullModel()->configuration->set_articles_updated_at_always) {
            $article->timestamps  = false;
        }
        ArticleHelper::saveProvider($article, $request);
        $actual_price = $article->price;
        $actual_stock = $article->stock;
        $article->status                            = 'active';
        $article->bar_code                          = $request->bar_code;
        $article->provider_code                     = $request->provider_code;
        $article->sub_category_id                   = $request->sub_category_id != 0 ? $request->sub_category_id : null;
        $article->brand_id                          = $request->brand_id != 0 ? $request->brand_id : null;
        $article->iva_id                            = $request->iva_id;
        $article->percentage_gain                   = $request->percentage_gain;
        $article->provider_price_list_id            = $request->provider_price_list_id != 0 ? $request->provider_price_list_id : null;
        if (!$article->apply_provider_percentage_gain && is_null($article->percentage_gain)) {
            $article->previus_price = $article->price;
            $article->price = $request->price;
            $article->timestamps = true;
        } else {
            $article->price = null;
        }
        if (strtolower($article->name) != strtolower($request->name)) {
            $article->name = ucfirst($request->name);
            $article->slug = ArticleHelper::slug($request->name);
        }
        $article->cost = $request->cost;
        $article->cost_in_dollars = $request->cost_in_dollars;
        $article->apply_provider_percentage_gain = $request->apply_provider_percentage_gain;
        if ($request->stock != '') {
            $article->stock = $request->stock;
            $article->stock += $request->new_stock;
            $article->stock_min = $request->stock_min;
        } else {
            $article->stock = null;
        }
        ArticleHelper::checkAdvises($article);
        $article->save();
        ArticleHelper::setTags($article, $request->tags);
        ArticleHelper::setDiscounts($article, $request->discounts);
        ArticleHelper::setDescriptions($article, $request->descriptions);
        ArticleHelper::setSizes($article, $request->sizes_id);
        ArticleHelper::setColors($article, $request->colors);
        ArticleHelper::setCondition($article, $request->condition_id);
        ArticleHelper::setSpecialPrices($article, $request);
        ArticleHelper::setDeposits($article, $request);
        $article = ArticleHelper::getFullArticle($article->id);
        $article->user->notify(new UpdatedArticle($article));
        return response()->json(['model' => $article], 200);
    }

    function updateProps(Request $request) {
        if ($request->from_filter) {
            $models = ArticleFilterHelper::filter($request->filter);
        } else {
            $models = ArticleHelper::getById($request->articles_ids);
        }
        $result = [];
        foreach ($models as $model) {
            if ($request->form['iva_id'] != 0) {
                $model->iva_id = $request->form['iva_id'];
            }
            if ($request->form['percentage_cost'] != '') {
                $model->cost += $model->cost * floatval($request->form['percentage_cost']) / 100;
            }
            if ($request->form['percentage_price'] != '') {
                $model->price += $model->price * floatval($request->form['percentage_price']) / 100;
            }
            if ($request->form['sub_category_id'] != 0) {
                $model->sub_category_id = $request->form['sub_category_id'];
            }
            $model->save();
            $result[] = ArticleHelper::getFullArticle($model->id);
        }
        return response()->json(['models' => $models], 200);
    }

    function updateProp(Request $request, $prop) {
        $articles = [];
        foreach ($request->articles_ids as $id) {
            $article = Article::find($id);
            $article->{$prop} = $request->{$prop};
            $article->save();
            $articles[] = ArticleHelper::getFullArticle($article->id);
        }
        return response()->json(['models' => $articles], 200);
    }

    function updateCategory(Request $request) {
        $articles = [];
        foreach ($request->articles_ids as $id) {
            $article = Article::find($id);
            $article->sub_category_id = $request->sub_category_id;
            $article->save();
            $articles[] = ArticleHelper::getFullArticle($article->id);
        }
        return response()->json(['models' => $articles], 200);
    }

    function updateBrand(Request $request) {
        $articles = [];
        foreach ($request->articles_ids as $id) {
            $article = Article::find($id);
            $article->brand_id = $request->brand_id;
            $article->save();
            $articles[] = ArticleHelper::getFullArticle($article->id);
        }
        return response()->json(['models' => $articles], 200);
    }

    function addImage(Request $request, $id) {
        $image = Image::create([
            'article_id'    => $id,
            'url'           => $request->image_url,
            'hosting_url'   => ImageHelper::saveHostingImage($request->image_url),
        ]);
        $article = ArticleHelper::getFullArticle($id);
        return response()->json(['model' => $article], 201);
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

    function descriptionsCopy(Request $request) {
        $article_from = Article::where('id', $request->from['id'])
                                ->with('descriptions')
                                ->first();
        $article_to = Article::find($request->to['id']);
        foreach ($request->descriptions_id as $description_id) {
            $description = Description::find($description_id);
            Description::create([
                'title'      => $description->title,
                'content'    => $description->content,
                'article_id' => $article_to->id,
            ]);
        }
        $article = ArticleHelper::getFullArticle($article_to->id);
        return response()->json(['model' => $article], 200);

    }

    function imagesCopy(Request $request) {
        $article_from = Article::where('id', $request->from)
                                ->with('descriptions')
                                ->first();
        $article_to = Article::find($request->to);
        foreach ($article_from->images as $image) {
            Image::create([
                'article_id' => $article_to->id,
                'url'        => $image->url,
            ]);
        }
        if ($request->copy_descriptions) {
            ArticleHelper::setDescriptions($article_to, $article_from->descriptions);
        }
        $article = ArticleHelper::getFullArticle($article_to->id);
        return response()->json(['model' => $article], 200);
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
        $article = ArticleHelper::getFullArticle($article->id);
        return response()->json(['model' => $article], 200);
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
        return response()->json(['models' => $articles], 200);
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
        $article = ArticleHelper::getFullArticle($article->id);
        return response()->json(['model' => $article], 200);
    }

    function setOnline($articles_id) {
        $article = Article::find($articles_id);
        if ($article->online) {
            $article->online = 0;
        } else {
            $article->online = 1;
        }
        $article->save();
        $article = ArticleHelper::getFullArticle($article->id);
        return response()->json(['model' => $article], 200);
    }

    function removeOnline($articles_id) {
        foreach (explode('-', $articles_id) as $id) {
            $article = Article::find($id);
            $article->online = 0;
            $article->save();
        }
    }

    function store(Request $request) {
        $article = new Article();
        $article->num = $this->num('articles');
        $article->bar_code = $request->bar_code;
        $article->provider_code = $request->provider_code;
        // if ($request->sub_category_id != 0) {
        //     $article->sub_category_id = $request->sub_category_id;
        // }
        $article->sub_category_id = $request->sub_category_id;
        // if ($request->brand_id != 0) {
        //     $article->brand_id = $request->brand_id;
        // }
        $article->brand_id                          = $request->brand_id;
        $article->name                              = ucfirst($request->name);
        $article->slug                              = ArticleHelper::slug($request->name);
        $article->cost                              = $request->cost;
        $article->cost_in_dollars                   = $request->cost_in_dollars;
        $article->apply_provider_percentage_gain    = $request->apply_provider_percentage_gain;
        $article->price                             = $request->price;
        $article->percentage_gain                   = $request->percentage_gain;
        $article->provider_price_list_id            = $request->provider_price_list_id != 0 ? $request->provider_price_list_id : null;
        if (isset($request->iva_id)) {
            $article->iva_id = $request->iva_id;
        }
        // if ($request->stock != '') {
        //     $article->stock = $request->stock;
        // }
        $article->stock = $request->stock;
        $article->stock_min = $request->stock_min;
        $article->user_id = $this->userId();
        if (isset($request->status)) {
            $article->status = $request->status;
        }
        $article->save();
        ArticleHelper::setTags($article, $request->tags);
        ArticleHelper::setDiscounts($article, $request->discounts);
        ArticleHelper::setDescriptions($article, $request->descriptions);
        ArticleHelper::setSizes($article, $request->sizes_id);
        ArticleHelper::setColors($article, $request->colors);
        ArticleHelper::setCondition($article, $request->condition_id);
        ArticleHelper::attachProvider($article, $request);
        ArticleHelper::setSpecialPrices($article, $request);
        ArticleHelper::setDeposits($article, $request);
        $article = ArticleHelper::getFullArticle($article->id);
        $article->user->notify(new CreatedArticle($article));
        return response()->json(['model' => $article], 201);
    }

    function import(Request $request) {
        $columns = GeneralHelper::getImportColumns($request);
        Log::info('columns:');
        Log::info($columns);
        Excel::import(new ArticlesImport($columns, $request->percentage, $request->provider_id), $request->file('models'));
    }

    function pdf($ids) {
        $articles = [];
        foreach (explode('-', $ids) as $id) {
            $articles[] = Article::find($id);
        }
        $articles = ArticleHelper::setPrices($articles);
        (new ArticleTicketPdf($articles));
    }

    function newArticle(Request $request) {
        $article = new Article();
        $article->user_id = $this->userId();
        $article->price = $request->price;
        if ($request->bar_code != '') {
            $article->bar_code = $request->bar_code;
        }
        if ($request->name != '') {
            $article->name = $request->name;
        }
        $article->save();
        $article = ArticleHelper::getFullArticle($article->id);
        return response()->json(['model' => $article], 201);
    }

    function destroy($id) {
        $article = Article::find($id);
        $article->status = 'inactive';
        $article->save();
    }

    function delete(Request $request) {
        foreach ($request->articles_id as $article_id) {
            $article = Article::find($article_id);
            if (
                count($article->sales) >= 1 
                || count($article->budgets) >= 1 
                || count($article->order_productions) >= 1
                || count($article->provider_orders) >= 1
                || count($article->recipes) >= 1
            ) {
                $article->status = 'inactive';
                $article->save();
            } else {
                $article->delete();
            }
        }
        return response(null, 200);
    }

    function deleteOffer($id) {
        $article = Article::find($id);
        $article->offer_price = null;
        $article->save();
    }

    function export() {
        return Excel::download(new ArticlesExport, 'comerciocity-articulos.xlsx');
    }
}
