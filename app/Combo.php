<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('articles');
    }

    function articles() {
        return $this->belongsToMany('App\Article')->withPivot('amount');
    }

    public function sales() {
        return $this->belongsToMany('App\Sale')->withPivot('amount', 'price');
    }
}
