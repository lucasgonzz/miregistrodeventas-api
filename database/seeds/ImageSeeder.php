<?php

use App\Image;
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
    	for ($article_id=1; $article_id <= 1000 ; $article_id++) { 
    		for ($j=0; $j < 2; $j++) { 
		        Image::create([
		        	'article_id' => $article_id,
		        	'url'        => 'v1614388222/articles/c7tspfd4lwvkvuhx70xv.jpg',
		        ]);
    		}
    	}
    }
}
