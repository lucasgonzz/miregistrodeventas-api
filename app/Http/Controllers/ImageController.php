<?php

namespace App\Http\Controllers;

use App\Article;
use App\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function delete($id) {
    	$image = Image::find($id);
    	$image->delete();
    	$article = Article::where('id', $image->article_id)
                            ->with('images')
                            ->with('category')
                            ->with('specialPrices')
                            ->with(['providers' => function($q) {
                                $q->orderBy('cost', 'asc');
                            }])
                            ->first();
    	return response()->json(['article' => $article], 200);
    }
}
