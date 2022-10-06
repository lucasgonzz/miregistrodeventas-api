<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionBeta extends Model
{
    protected $guarded = [];

    function plans() {
        return $this->belongsToMany('App\Plan');
    }

    function extencion() {
        return $this->belongsTo('App\Extencion');
    }
}
