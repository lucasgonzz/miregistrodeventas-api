<?php

use App\Article;
use App\CurrentAcount;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\DiscountHelper;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Http\Controllers\Helpers\Sale\SaleHelper;
use App\Sale;
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
        $dias_no_ventas = [3,4,9,10,14,16,15,13];
        $now = Carbon::now();
        $total_ventas = 10;
        $user_id = 3;
        for ($i=1; $i <= $total_ventas; $i++) { 
            // for ($j=0; $j < 5; $j++) { 
                $num_sale = SaleHelper::numSale(1);
                $sale = Sale::create([
                    'user_id' => $user_id,
                    'num_sale' => $num_sale,
                    'percentage_card' => null,
                    'client_id' => 2,
                    'sale_type_id' => 1,
                    'created_at' => Carbon::now()->subDays($total_ventas-$i),
                ]);
                $articles = Article::where('user_id', $user_id)
                                    ->take(33)
                                    ->get();
                foreach ($articles as $article) {
                    $sale->articles()->attach($article->id, [
                                                'amount'      => 2,
                                                'cost'        => $article->cost,
                                                'price'       => $article->price,
                                            ]);
                }
                $discounts = DiscountHelper::getDiscountsFromDiscountsId([1]);
                SaleHelper::attachDiscounts($sale, $discounts, $i);
                $helper = new SaleHelper_Commissioners($sale, $discounts, $total_ventas-$i);
                $helper->attachCommissionsAndCurrentAcounts();
            // }
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
