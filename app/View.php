<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    protected $guarded = [];

    function buyer() {
    	return $this->belongsTo('App\Buyer');
    }
}
