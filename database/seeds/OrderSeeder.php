<?php

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
            Order::create([
                'buyer_id'              => $model['buyer_id'],
                'order_status_id'       => $model['order_status_id'],
                'deliver'               => $model['deliver'],
                'created_at'            => $model['created_at'],
                'user_id'               => $user->id,
            ]);
        }
    }
}
