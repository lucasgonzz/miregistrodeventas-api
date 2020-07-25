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

    function search($query) {
        $user = Auth()->user();
        $articles = Article::where('user_id', $this->getArticleOwnerId())
                            ->where('bar_code', $query)
                            ->with('images');
        if ($user->hasRole('commerce')) {
            $articles = $articles->with('providers');
        }
        $articles = $articles->get();

        if (count($articles) == 0) {
            $articles = Article::where('user_id', $this->getArticleOwnerId())
                                ->where('name', 'LIKE', "%$query%")
                                ->with('images');
            if ($user->hasRole('commerce')) {
                $articles = $articles->with('providers');
            }
            $articles = $articles->get();
        } 
        return $articles;
    }

    function preSearch($query, $only_without_bar_code = 0, $with_providers = 0) {
        $only_without_bar_code = (bool)$only_without_bar_code;
        $with_providers = (bool)$with_providers;
        if ($only_without_bar_code) {
            if ($with_providers) {
                $articles = Article::where('user_id', $this->getArticleOwnerId())
                                    ->where('name', 'LIKE', "%$query%")
                                    ->whereNull('bar_code')
                                    ->with('providers')
                                    ->limit(5)
                                    ->get();
            } else {
                $articles = Article::where('user_id', $this->getArticleOwnerId())
                                    ->where('name', 'LIKE', "%$query%")
                                    ->whereNull('bar_code')
                                    ->limit(5)
                                    ->get();
            }
        } else {
            if ($with_providers) {
                $articles = Article::where('user_id', $this->getArticleOwnerId())
                                    ->where('name', 'LIKE', "%$query%")
                                    ->with('providers')
                                    ->limit(5)
                                    ->get();
            } else {
                $articles = Article::where('user_id', $this->getArticleOwnerId())
                                    ->where('name', 'LIKE', "%$query%")
                                    ->limit(5)
                                    ->get();
            }
        }
        return $articles;
    }
}
