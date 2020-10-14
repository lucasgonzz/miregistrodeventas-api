<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    function articles() {
    	return $this->belongsToMany('App\Article')->withPivot('cost', 'price', 'amount');
    }

    function buyer() {
    	return $this->belongsTo('App\Buyer');
    }
}
