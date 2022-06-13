<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $guarded = [];

    function plans() {
        return $this->belongsToMany('App\Plan');
    }

    function extencion() {
        return $this->belongsTo('App\Extencion');
        // return $this->belongsTo('App\Extencion', 'extencion_id');
    }
}
