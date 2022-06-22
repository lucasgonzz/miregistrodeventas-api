<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('articles.images', 'articles.variants', 'articles.colors', 'articles.sizes', 'address', 'cupons', 'buyer', 'payment_method', 'delivery_zone');
    }

    function articles() {
    	return $this->belongsToMany('App\Article')->withPivot('cost', 'price', 'amount', 'variant_id', 'color_id', 'size_id', 'with_dolar');
    }

    function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    function cupons() {
        return $this->hasMany('App\Cupon');
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
