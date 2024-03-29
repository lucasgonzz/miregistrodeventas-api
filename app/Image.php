<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    public $guarded = [];

    function color() {
    	return $this->belongsTo('App\Color');
    }
}
