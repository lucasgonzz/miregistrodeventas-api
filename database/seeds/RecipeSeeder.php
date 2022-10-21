<?php

use App\Article;
use App\Recipe;
use App\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->mcElectronica();
    }

    function mcElectronica() {
        $user = User::where('company_name', 'mc electronica')->first();
        $article = Article::where('user_id', $user->id)
                            ->where('name', 'plaqueta de rosario')
                            ->first();
        $recipe = Recipe::create([
            'article_id'    => $article->id,
            'user_id'       => $user->id
        ]);
        $articles = [
            [
                'name'                          => 'Tornillo num 6',
                'order_production_status_id'    => 6,
                'amount'                        => 4,
            ],
            [
                'name'                          => 'Boton chico blanco',
                'order_production_status_id'    => 6,
                'amount'                        => 2,
            ],
            [
                'name'                          => 'Cable 10cm',
                'order_production_status_id'    => 7,
                'amount'                        => 2,
            ],
            [
                'name'                          => 'Carcaza negra',
                'order_production_status_id'    => 8,
                'amount'                        => 1,
            ],
        ];
        foreach ($articles as $article) {
            $art = Article::where('user_id', $user->id)
                            ->where('name', $article['name'])
                            ->first();
            $recipe->articles()->attach($art->id, [
                                    'order_production_status_id'    => $article['order_production_status_id'],
                                    'amount'                        => $article['amount'],
                                ]);
        }
    }
}
