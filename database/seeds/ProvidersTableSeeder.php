<?php

use Illuminate\Database\Seeder;
use App\Provider;
use App\Article;
use Carbon\Carbon;


class ProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$providers = [
    		'Buenos Aires',
    		'Rosario',
    		'Victoria',
    		'Galarza',
    		'Cordoba',
    		'Brazil',
    		'Tucuman',
    		'Santa Cruz',
    		'La Pampa',
    		'Gualeguay',
    	];

    	foreach ($providers as $provider) {
	        Provider::create([
	        	'name' => $provider,
	        	'user_id' => 2,
	        ]);
    	}

    	$articles = Article::where('user_id', 2)->get();
    	foreach ($articles as $article) {
            $amount = $article->stock;
            $cost = $article->cost;
            $price = $article->price;

            $providers = [];
            $amounts = [];
            $costs = [];
            $prices = [];
			$created_ats = [];
			for ($i=0; $i < 3 ; $i++) { 
                if (isset($amounts[1])) {
                    $amounts[] = $amount - $amounts[1];
                    $prices[] = $price;
                    $costs[] = $cost;
                    $created_ats[] = Carbon::now()->subMonth();
                } else
                if (isset($amounts[0])) {
                    $amounts[] = $amount - $amounts[0];
                    $prices[] = $price - 150;
                    $costs[] = $cost - 150;
                    $created_ats[] = Carbon::now()->subMonths(5);
                } else {
                    $amounts[] = $amount - 6;
                    $prices[] = $price - 350;
                    $costs[] = $cost - 350;
                    $created_ats[] = Carbon::now()->subMonths(8);
                }

                $provider_id = rand(0, 10);
                while (in_array($provider_id, $providers)) {
                    $provider_id = rand(0, 10);
                }
                $providers[] = $provider_id;
			}
            for ($i=0; $i < 3; $i++) { 
                $article->providers()->attach($providers[$i], [
                                                'amount' => $amounts[$i],
                                                'cost' => $costs[$i],
                                                'price' => $prices[$i],
                                                'created_at' => $created_ats[$i]
                                                ]);
            }
    	}
    }
}
