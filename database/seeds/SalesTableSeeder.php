<?php

use App\Address;
use App\Article;
use App\Client;
use App\CurrentAcount;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Http\Controllers\Helpers\SaleHelper;
use App\Sale;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->pinocho();
        $this->kasAberturas();
        // $this->withAllArticlesIva('colman');
        $this->colman();
        return;
        $dias_no_ventas = [3,4,9,10,14,16,15,13];
        $now = Carbon::now();
        $total_ventas = 1;
        $user = User::where('company_name', 'kas aberturas')->first();
        $client = Client::where('name', 'lucas')->first();
        for ($i=1; $i <= $total_ventas; $i++) { 
            // for ($j=0; $j < 5; $j++) { 
                $num_sale = SaleHelper::numSale($user->id);
                $sale = Sale::create([
                    'user_id' => $user->id,
                    'num_sale' => $num_sale,
                    'percentage_card' => null,
                    'client_id' => $client->id,
                    'sale_type_id' => 1,
                    // 'created_at' => Carbon::now()->subDays($total_ventas-$i),
                ]);
                $articles = Article::where('user_id', $user->id)
                                    ->take(1)
                                    ->get();
                foreach ($articles as $article) {
                    $sale->articles()->attach($article->id, [
                                                'amount'      => 2,
                                                'cost'        => $article->cost,
                                                'price'       => $article->final_price,
                                            ]);
                }
                $discounts = DiscountHelper::getDiscountsFromDiscountsId([1]);
                SaleHelper::attachDiscounts($sale, $discounts, $i);
                $helper = new SaleHelper_Commissioners($sale, $discounts, false);
                $helper->attachCommissionsAndCurrentAcounts();
            // }
        }
    }

    function pinocho() {
        $user = User::where('company_name', 'pinocho')->first();
        $address = Address::where('user_id', $user->id)->first();
        for ($day=1; $day < 5; $day++) { 
            $sale = Sale::create([
                'user_id' => $user->id,
                'num_sale' => SaleHelper::numSale($user->id),
                'percentage_card' => null,
                // 'client_id' => $client->id,
                'sale_type_id' => 1,
                'created_at' => Carbon::now()->subDays($day),
                'address_id' => $address->id,
            ]);
            $articles = Article::where('user_id', $user->id)
                                ->take(2)
                                ->get();
            foreach ($articles as $article) {
                $sale->articles()->attach($article->id, [
                                            'amount'      => 2,
                                            'cost'        => $article->cost,
                                            'price'       => $article->final_price,
                                        ]);
            }
        }
    }

    function kasAberturas() {
        $user = User::where('company_name', 'kas aberturas')->first();
        $address = Address::where('user_id', $user->id)->first();
        for ($day=1; $day < 5; $day++) { 
            $sale = Sale::create([
                'user_id' => $user->id,
                'num_sale' => SaleHelper::numSale($user->id),
                'percentage_card' => null,
                'created_at' => Carbon::now()->subDays($day),
            ]);
            $articles = Article::where('user_id', $user->id)
                                ->take(2)
                                ->get();
            // $articles = ArticleHelper::setPrices($articles);
            foreach ($articles as $article) {
                $sale->articles()->attach($article->id, [
                                            'amount'      => 2,
                                            'cost'        => $article->cost,
                                            'price'       => $article->final_price,
                                        ]);
            }
        }
    }

    function withAllArticlesIva($company_name) {
        $user = User::where('company_name', $company_name)->first();
        $clients = Client::where('user_id', $user->id)->get();
        foreach ($clients as $client) {
            $sale = Sale::create([
                'user_id'           => $user->id,
                'num_sale'          => SaleHelper::numSale($user->id),
                'percentage_card'   => null,
                'created_at'        => Carbon::now(),
                'client_id'         => $client->id,
            ]);
            $articles = Article::where('user_id', $user->id)
                                ->get();
            // $articles = ArticleHelper::setPrices($articles);
            foreach ($articles as $article) {
                $sale->articles()->attach($article->id, [
                                            'amount'      => 2,
                                            'cost'        => $article->cost,
                                            'price'       => $article->final_price,
                                        ]);
            }
        }
        $sale = Sale::create([
            'user_id'           => $user->id,
            'num_sale'          => SaleHelper::numSale($user->id),
            'percentage_card'   => null,
            'created_at'        => Carbon::now(),
        ]);
        $articles = Article::where('user_id', $user->id)
                            ->get();
        // $articles = ArticleHelper::setPrices($articles);
        foreach ($articles as $article) {
            $sale->articles()->attach($article->id, [
                                        'amount'      => 2,
                                        'cost'        => $article->cost,
                                        'price'       => $article->final_price,
                                    ]);
        }
    }

    function colman() {
        $user = User::where('company_name', 'colman')->first();
        $employees = User::where('owner_id', $user->id)
                        ->get();
        for ($day=1; $day < 5; $day++) { 
            foreach ($employees as $employee) {
                $sale = Sale::create([
                    'user_id'           => $user->id,
                    'num_sale'          => SaleHelper::numSale($user->id),
                    'percentage_card'   => null,
                    'created_at'        => Carbon::now()->subDays($day),
                    'employee_id'       => $employee->id,
                ]);
                $articles = Article::where('user_id', $user->id)
                                    ->take(2)
                                    ->get();
                // $articles = ArticleHelper::setPrices($articles);
                foreach ($articles as $article) {
                    $sale->articles()->attach($article->id, [
                                                'amount'      => 2,
                                                'cost'        => $article->cost,
                                                'price'       => $article->final_price,
                                            ]);
                }
            }
        }
    }

    public function getClient($user_id) {
        $with_client = (bool)rand(0,1);
        if ($with_client) {
            if ($user_id == 2) {
                return rand(1, 60);
            } else {
                return rand(60, 120);
            }
        }
        return null;
    }
}
