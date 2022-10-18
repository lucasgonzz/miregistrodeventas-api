<?php

use App\Article;
use App\OrderProduction;
use App\User;
use Illuminate\Database\Seeder;

class OrderProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('company_name', 'colman')->first();
        $article = Article::where('user_id', $user->id)
                            ->where('name', 'plaqueta de rosario')
                            ->first();
        $order_production = OrderProduction::create([
            'num'   => 1,
            'order_production_status_id' => 6,
            'client_id' => null,
            'user_id' => $user->id,
        ]);
        $order_production->articles()->attach($article->id, [
            'amount' => 10,
        ]);
    }
}
