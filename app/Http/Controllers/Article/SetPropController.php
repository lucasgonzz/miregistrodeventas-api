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

    function deleteOffer($id) {
        $article = Article::find($id);
        $article->offer_price = null;
        $article->save();
    }
}
