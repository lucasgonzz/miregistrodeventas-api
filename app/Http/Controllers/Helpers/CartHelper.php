<?php

namespace App\Http\Controllers\Helpers;

use App\Cart;

class CartHelper {

    static function detachArticulosFaltantes($articulos_faltantes, $order) {
        $cart = Cart::where('order_id', $order->id)->first();
        if (count($articulos_faltantes) >= 1) {
            $cart->articles()->detach();
            $is_articulo_faltante = false;
            foreach ($order->articles as $article) {
                foreach ($articulos_faltantes as $articulo_faltante) {
                    if ($article->key == $articulo_faltante['key']) {
                        $is_articulo_faltante = true;
                    }
                }
                if (!$is_articulo_faltante) {
                    $cart->articles()->attach($article->id, [
                                        'variant_id' => $article->pivot->variant_id,
                                        'amount' => $article->pivot->amount,
                                        'price' => $article->pivot->price,
                                    ]);
                }
                $is_articulo_faltante = false;
            }
        }
    }

}