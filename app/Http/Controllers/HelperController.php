<?php

namespace App\Http\Controllers;

use App\Article;
use App\Budget;
use App\BudgetStatus;
use App\Client;
use App\CurrentAcount;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\CurrentAcountHelper;
use App\Http\Controllers\Helpers\ImageHelper;
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
        }
    }

    function setClientsSaldos($company_name) {
        $user = User::where('company_name', $company_name)->first();
        $clients = Client::where('user_id', $user->id)->get();
        foreach ($clients as $client) {
            $last_current_acount = CurrentAcount::where('client_id', $client->id)
                                                ->orderBy('created_at', 'DESC')
                                                ->first();
            if (!is_null($last_current_acount)) {
                $client->saldo = $last_current_acount->saldo;
            } else {
                echo($client->name.' no tenia current_acount </br>');
                $client->saldo = 0;
            }
            $client->save();
            echo('Setenado saldo de '.$client->name.' a $'.$client->saldo.'</br>');
            echo('------------------------------------------------------------------</br>');
        }
    }

    function setArticlesHostingImages($company_name) {
        $user = User::where('company_name', $company_name)->first();
        $articles = Article::where('user_id', $user->id)
                            ->get();
        foreach ($articles as $article) {
            if (!is_null($article->images)) {
                foreach ($article->images as $image) {
                    if (is_null($image->hosting_url)) {
                        $image->hosting_url = ImageHelper::saveHostingImage($image->url);
                        $image->save();
                        echo 'Articulo: '.$article->name.'. Hosting_image: '.$image->hosting_url.' </br>';
                        echo('------------------------------------------------------------------</br>');
                    }
                }
            }
        }
    }

    function setUserHostingImage($company_name) {
        $user = User::where('company_name', $company_name)->first();
        if (is_null($user->hosting_image_url)) {
            $user->hosting_image_url = ImageHelper::saveHostingImage($user->image_url);
            $user->save();
            echo 'Usuario: '.$user->company_name.'. Hosting_image: '.$user->hosting_image_url.' </br>';
            echo('------------------------------------------------------------------</br>');
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
