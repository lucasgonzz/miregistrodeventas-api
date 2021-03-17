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
        $artices_count = Article::where('user_id', 1)->count(); 
    	for ($article_id=1; $article_id <= $artices_count ; $article_id++) { 
    		for ($j=0; $j < 2; $j++) { 
		        Image::create([
		        	'article_id' => $article_id,
		        	'url'        => 'v1614388222/articles/c7tspfd4lwvkvuhx70xv.jpg',
		        ]);
    		}
    	}
    }
}
