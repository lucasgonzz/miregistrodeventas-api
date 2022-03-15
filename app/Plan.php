<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    function permissions() {
        return $this->belongsToMany('App\Permission');
    }

    function features() {
        return $this->belongsToMany('App\Feature')->withPivot('active');
    }
}
