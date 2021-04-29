<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{

	protected $guarded = [];

    function articles() {
    	return $this->belongsToMany('App\Article')->withPivot('price', 'amount', 'variant_id');
    }
}
