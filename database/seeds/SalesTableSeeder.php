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
        $num_sale = 0;
        $num_sale++;
        $now = Carbon::now();
        $total_ventas = 10;
        for ($i=1; $i <= $total_ventas; $i++) { 
            $sale = Sale::create([
                'user_id' => 1,
                'num_sale' => $i,
                'percentage_card' => null,
                'client_id' => 2,
                'sale_type_id' => 1,
                'created_at' => Carbon::now()->subDays($total_ventas-$i),
            ]);
            $articles = Article::where('user_id', 1)
                                ->take(30)
                                ->get();
            foreach ($articles as $article) {
                $sale->articles()->attach($article->id, 
                    [
                        'amount'      => 2,
                        'cost'        => $article->cost,
                        'price'       => $article->price,
                    ]
                );
            }
            $discounts = DiscountHelper::getDiscountsFromDiscountsId([1]);
            SaleHelper::attachDiscounts($sale, $discounts);
            $helper = new SaleHelper_Commissioners($sale, $discounts);
            $helper->attachCommissionsAndCurrentAcounts();
        }
    }

    public function getClient($user_id) {
        $with_client = (bool)rand(0,1);
        if ($with_client) {
            if ($user_id == 1) {
                return rand(1, 60);
            } else {
                return rand(60, 120);
            }
        }
        return null;
    }
}
