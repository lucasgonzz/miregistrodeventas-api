<?php

use App\Article;
use App\Http\Controllers\Controller;
use App\Provider;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;


class ProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->colman();
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
        $addresses = [
            'San antonio 45',
            'Cruz Migue 100',
            '25 de mayo 50',
            'Galarza 557',
            'Cordoba 35',
            'Vicotira 110',
            'San Martin 1020',
            'Maipu 695',
            'Carmen Gadea 787',
            'Pellegrini 1876',
        ];
        $users = User::where('company_name', 'Fiushh')
                    ->orWhere('company_name', 'kas aberturas')
                    ->get();
        $index = 0;
        $ct = new Controller();
        foreach ($users as $user) {
        	foreach ($providers as $provider) {
    	        Provider::create([
                    'num'     => $ct->num('providers', $user->id), 
    	        	'name'    => $provider,
                    'address' => $addresses[$index],
                    'email'   => 'lucasgonzalez5500@gmail.com',
    	        	'user_id' => $user->id,
    	        ]);
                $index++;
        	}
            $index = 0;
        }

    	$articles = Article::where('user_id', $user->id)->get();
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

    function colman() {
        $ct = new Controller();
        $user = User::where('company_name', 'colman')->first();
        $models = [
            [
                'name'              => 'Buenos Aires',
                'percentage_gain'   => 50,
            ],
            [
                'name'              => 'Rosario',
                'percentage_gain'   => 100,
            ],
        ];
        foreach ($models as $model) {
            Provider::create([
                'num'               => $ct->num('providers', $user->id), 
                'name'              => $model['name'],
                'percentage_gain'   => $model['percentage_gain'],
                'email'             => 'lucasgonzalez5500@gmail.com',
                'address'           => 'Calle 123',
                'user_id'           => $user->id,
            ]);
        }
    }
}
