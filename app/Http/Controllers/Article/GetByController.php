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

    function getByBarCode($bar_code) {
        $user = Auth()->user();
        if ($user->hasRole('commerce')) {
            return Article::where('user_id', $this->getArticleOwnerId())
                            ->where('bar_code', $bar_code)
                            ->with('providers')
                            ->first();
        } else {
            return Article::where('user_id', $this->getArticleOwnerId())
                            ->where('bar_code', $bar_code)
                            ->first();
        }
    }

    function getByName($name) {
        $user = Auth()->user();
        if ($user->hasRole('commerce')) {
            return Article::where('user_id', $this->getArticleOwnerId())
                            ->where('name', $name)
                            ->whereNull('bar_code')
                            ->with('providers')
                            ->first();
        } else {
            return Article::where('user_id', $this->getArticleOwnerId())
                            ->where('name', $name)
                            ->whereNull('bar_code')
                            ->first();
        }
    }

}
