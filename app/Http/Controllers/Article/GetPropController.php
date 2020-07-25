<?php

namespace App\Http\Controllers\Article;

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

class GetPropController extends Controller
{

    function withMarker($id) {
        return Article::where('id', $id)
                        ->with('marker')
                        ->first();
    }

    function getAvailables() {
        return Article::where('user_id', $this->getArticleOwnerId())
                        ->select('bar_code', 'name', 'price', 'uncontable')
                        ->get();
    }

    function getBarCodes(Request $request) {
        return Article::where('user_id', $this->getArticleOwnerId())
                        ->whereNotNull('bar_code')
                        ->orderBy('id', 'DESC')
                        ->pluck('bar_code');
    }

    function getNames() {
        return Article::where('user_id', $this->getArticleOwnerId())
                        ->orderBy('id', 'DESC')
                        ->pluck('name');
    }
}
