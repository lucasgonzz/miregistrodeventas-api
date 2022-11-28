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
use App\Order;
use App\OrderProduction;
use App\OrderStatus;
use App\Title;
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
        $id = 1;
        $user = User::where('company_name', $company_name)->first();
        $imagenes_actualizadas = 0;
        while ($id == 1 || count($articles) == 20) {
            echo('--------------------------------------------------VUELTA-------------------------------------------------- </br>');
            $articles = Article::where('user_id', $user->id)
                                ->where('id', '>=', $id)
                                ->take(20)
                                ->orderBy('id', 'ASC')
                                ->get();
            foreach ($articles as $article) {
                if (!is_null($article->images)) {
                    foreach ($article->images as $image) {
                        if (is_null($image->hosting_url)) {
                            $image->hosting_url = ImageHelper::saveHostingImage($image->url);
                            $image->save();
                            echo 'Articulo id '.$article->id.': '.$article->name.'. Hosting_image: '.$image->hosting_url.' </br>';
                            echo('------------------------------------------------------------------</br>');
                            $imagenes_actualizadas++;
                        }
                    }
                }
            }
            $id = $articles[count($articles)-1]->id;
            // if ($imagenes_actualizadas >= 30) {
            //     echo ('---------------------- SE ACTUALIZARON 30 ----------------------');
            //     break;
            // }
        }
        // if ($imagenes_actualizadas) {
        //     echo ('---------------------- ACTUALIZANDO PAGINA ----------------------');
        //     return back();
        // }
        echo('------------------- TERMINO ----------------------');
    }

    function setTitlesHostingImages($company_name) {
        $user = User::where('company_name', $company_name)->first();
        $titles = Title::where('user_id', $user->id)
                            ->get();
        foreach ($titles as $title) {
            if (is_null($title->hosting_image_url)) {
                $title->hosting_image_url = ImageHelper::saveHostingImage($title->image_url);
                $title->save();
                echo 'Titulo Hosting_image: '.$title->hosting_image_url.' </br>';
                echo('------------------------------------------------------------------</br>');
            }
        }
        echo('------------------- TERMINO ----------------------');
    }

    function setOrdersStatus($company_name) {
        $user = User::where('company_name', $company_name)->first();
        $models = Order::where('user_id', $user->id)
                            ->get();
        $sin_confirmar = OrderStatus::where('name', 'Sin confirmar')->first();
        $confirmado = OrderStatus::where('name', 'Confirmado')->first();
        $terminado = OrderStatus::where('name', 'Terminado')->first();
        $entregado = OrderStatus::where('name', 'Entregado')->first();
        $cancelado = OrderStatus::where('name', 'Cancelado')->first();
        foreach ($models as $model) {
            if ($model->status == 'unconfirmed') {
                $model->order_status_id = $sin_confirmar->id;
            } else if ($model->status == 'confirmed') {
                $model->order_status_id = $confirmado->id;
            } else if ($model->status == 'finished') {
                $model->order_status_id = $terminado->id;
            } else if ($model->status == 'delivered') {
                $model->order_status_id = $entregado->id;
            } else if ($model->status == 'canceled') {
                $model->order_status_id = $cancelado->id;
            }
            $model->save();
            echo 'Pedido '.$model->id.' con status: '.$model->status.' actualizado a estado '.$model->order_status->name.' </br>';
            echo('------------------------------------------------------------------</br>');
        }
        echo('------------------- TERMINO ----------------------');
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
