<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    function permissions() {
        return $this->belongsToMany('App\PermissionBeta');
    }

    function features() {
        return $this->belongsToMany('App\Feature')->withPivot('active');
    }
}
