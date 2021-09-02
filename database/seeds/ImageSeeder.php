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
        $images = [
            'v1616079010/articles/ztaa7kyj1cfqoj8fmsjp.jpg', 
            'v1616538802/articles/kboz26romcgmiswoocjw.jpg', 
            'v1615988247/articles/cumc9e2hifffpr498nz6.jpg', 
            'v1615989993/articles/ofzfakuwnre6qy6plzw0.jpg',
            'v1615988968/articles/b6gcidfseqa3f59zyjr1.jpg',
            'v1616507853/articles/guuyxregqgje3nhmmefj.jpg',
            'v1616506825/articles/kcjbnqtkphlyacfc3bks.jpg'
        ];
        $articles = Article::where('user_id', 1)->get(); 
        foreach ($articles as $article) {
    		for ($j=0; $j < 2; $j++) { 
		        Image::create([
		        	'article_id' => $article->id,
		        	'url'        => $images[rand(0,6)],
		        ]);
    		}
        }
    }
}
