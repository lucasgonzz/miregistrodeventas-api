<?php

namespace App\Http\Controllers;

use App\Article;
use App\Buyer;
use App\Mail\ArticulosNuevos;
use App\Mail\OrderConfirmed;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{

    function order() {
        $buyer = Buyer::find(1);
        $commerce = User::find(1);
        Mail::to($buyer)->send(new OrderConfirmed($buyer, $commerce));
        echo "enviado";
    }

    function articles($ids) {
        $ids = explode('-', $ids);
        $articles = [];
        foreach ($ids as $id) {
            $articles[] = Article::where('id', $id)
                                    ->with('images')
                                    ->first();
        }
        $user = User::find(1);
        // $user = User::find($this->userId());
        $buyers = Buyer::where('id', 1)
                        ->get();
        return new ArticulosNuevos($articles, $buyers[0], $user);
        // return view('markdown_view', ['buyer' => $buyers[0], 'articles' => $articles, 'user' => $user]);
        foreach ($buyers as $buyer) {
            Mail::to($buyer)->send(new ArticulosNuevos($articles, $buyer, $user));
        }
        echo "enviado";
    }

}
