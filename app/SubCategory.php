<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $guarded = [];

    function category() {
    	return $this->belongsTo('App\Category');
    }
}
