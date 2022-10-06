<?php

namespace App\Http\Controllers;

use App\Article;
use App\Budget;
use App\BudgetStatus;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\OrderProduction;
use App\User;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    function setArticlesFromBudgets($company_name) {
        $user = User::where('company_name', $company_name)->first();
        $budgets = Budget::where('user_id', $user->id)->get();
        $sin_confirmar = BudgetStatus::where('name', 'Sin confirmar')->first();
        $confirmado = BudgetStatus::where('name', 'Confirmado')->first();
        foreach ($budgets as $budget) {
            if ($budget->status == 'unconfirmed') {
                $budget->budget_status_id = $sin_confirmar->id;
                echo 'Se puso en NO confirmado </br>';
            } else {
                $budget->budget_status_id = $confirmado->id;
                echo 'Se puso en CONFIRMADO </br>';
            }
            $budget->save();
            // $budget->articles()->detach();
            // foreach ($budget->products as $product) {
            //     $article = Article::create([
            //         'bar_code'  => $product->bar_code,
            //         'name'      => $product->name,
            //         'slug'      => ArticleHelper::slug($product->name),
            //         'status'    => 'inactive',
            //         'user_id'   => $user->id,
            //     ]);
            //     echo "Se creo articulo: ".$article->name.'. Con precio: '.$product->price.', cantidad: '.$product->amount.', bonus: '.$product->bonus.' y locacion: '.$product->location;
            //     $budget->articles()->attach($article->id, [
            //                             'amount'    => $product->amount,
            //                             'price'     => $product->price,
            //                             'bonus'     => $product->bonus,
            //                             'location'  => $product->location,
            //                         ]);
            // }
        }
    }

    function setArticlesFromOrderProductions($company_name) {
        $user = User::where('company_name', $company_name)->first();
        $order_productions = OrderProduction::where('user_id', $user->id)->get();
        foreach ($order_productions as $order_production) {
            $order_production->order_production_status_id = $order_production->budget->client_id;
            $order_production->save();
            if ($order_production->budget->delivery_and_placement == 1) {
                $order_production->observations = 'Con colocacion';
                $order_production->save();
            }
            $order_production->client_id = $order_production->budget->client_id;
            $order_production->save();
            $order_production->articles()->detach();
            foreach ($order_production->budget->articles as $article) {
                if (!is_null($article->pivot->delivered)) {
                    echo 'Se entregaron '.$article->pivot->delivered.' del articulo '.$article->name.' el '.date_format($order_production->created_at, 'd/m/Y');
                }
                $order_production->articles()->attach($article->id, [
                                        'amount'    => $article->pivot->amount,
                                        'price'     => $article->pivot->price,
                                        'bonus'     => $article->pivot->bonus,
                                        'location'  => $article->pivot->location,
                                        'delivered' => $article->pivot->delivered,
                                    ]);
            }
        }
    }
}
