<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = [];
    
    function articles() {
        return $this->belongsToMany('App\Article');
    }
}
