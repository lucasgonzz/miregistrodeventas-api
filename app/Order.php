<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    function articles() {
    	return $this->belongsToMany('App\Article')->withPivot('cost', 'price', 'amount', 'variant_id', 'color_id', 'size_id');
    }

    function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    function cupons() {
        return $this->hasMany('App\Cupon');
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
