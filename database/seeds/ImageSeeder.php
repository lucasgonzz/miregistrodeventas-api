<?php

use App\Image;
use App\Article;
use Illuminate\Database\Seeder;


class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$images = ['foto.jpg', 'tostadora.jpeg'];
        $articles = Article::where('user_id', 2)->get(); 
        foreach ($articles as $article) {
    		for ($j=0; $j < 2; $j++) { 
		        Image::create([
		        	'article_id' => $article->id,
		        	'url'        => 'v1616079010/articles/ztaa7kyj1cfqoj8fmsjp.jpg',
		        ]);
    		}
        }
    }
}
