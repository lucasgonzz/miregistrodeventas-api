<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $guarded = [];

    function category() {
    	return $this->belongsTo('App\Category');
    }
    
    public function articles() {
        return $this->hasMany('App\Article');
    }

    function views() {
        return $this->morphMany('App\View', 'viewable');
    }
}
