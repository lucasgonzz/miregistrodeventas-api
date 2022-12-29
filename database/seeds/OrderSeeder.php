<?php

use App\Article;
use App\Buyer;
use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('company_name', 'colman')->first();
        $buyer = Buyer::where('user_id', $user->id)->first();
        $models = [
            [
                'buyer_id'          => $buyer->id,
                'order_status_id'   => 1,
                'deliver'           => 0,
                'created_at'        => Carbon::now()->subDays(2),
            ],
        ];
        foreach ($models as $model) {
            $order = Order::create([
                'num'                   => 1,
                'buyer_id'              => $model['buyer_id'],
                'order_status_id'       => $model['order_status_id'],
                'deliver'               => $model['deliver'],
                'created_at'            => $model['created_at'],
                'user_id'               => $user->id,
            ]);
            $articles = Article::where('user_id', $user->id)
                                ->get();
            foreach ($articles as $article) {
                $order->articles()->attach($article->id, [
                    'amount'    => 2,
                    'price'     => $article->final_price,
                ]); 
             }     
        }
    }
}
