<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    function permissions() {
        return $this->belongsToMany('App\Permission');
    }
}
