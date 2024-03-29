<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    function order() {
    	return $this->belongsTo('App\Order');
    }
}
