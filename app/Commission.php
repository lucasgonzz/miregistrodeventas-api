<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $guarded = [];

    function commissioner() {
    	return $this->belongsTo('App\Commissioner');
    }
}
