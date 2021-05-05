<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Image;
use App\Variant;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function delete($id) {
    	$image = Image::find($id);
        $this->deleteVariant($image);
    	$image->delete();
        $article = ArticleHelper::getFullArticle($image->article_id);
    	return response()->json(['article' => $article], 200);
    }

    function deleteVariant($image) {
        $variant = Variant::where('url', $image->url)->first();
        if ($variant) {
            $variant->delete();
        }
    }
}
