<?php

use App\Article;
use App\PricesList;
use Illuminate\Database\Seeder;

class PricesListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $prices_list = new PricesList();
        $prices_list->name = 'Lista de precios';
        $prices_list->user_id = 2;
        $prices_list->save();
        $articles = Article::where('user_id', 2)
                            ->get();
        foreach ($articles as $article) {
            $prices_list->articles()->attach($article['id']);
        }
    }
}
