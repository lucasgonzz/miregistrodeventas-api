<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('order_status', 'articles.images', 'articles.colors', 'articles.sizes', 'address', 'cupon', 'buyer', 'payment_method.type', 'delivery_zone');
    }

    function articles() {
        return $this->belongsToMany('App\Article')->withPivot('cost', 'price', 'amount', 'variant_id', 'color_id', 'size_id', 'with_dolar');
    }

    function order_status() {
        return $this->belongsTo('App\OrderStatus');
    }

    function cupon() {
        return $this->belongsTo('App\Cupon');
    }

    function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    function payment_method() {
        return $this->belongsTo('App\PaymentMethod');
    }

    function delivery_zone() {
        return $this->belongsTo('App\DeliveryZone');
    }

    function user() {
        return $this->belongsTo('App\User');
    }

    function payment() {
        return $this->hasOne('App\Payment');
    }

    function address() {
        return $this->belongsTo('App\Address');
    }
}
