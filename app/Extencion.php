<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Extencion extends Model
{
    protected $guarded = [];

    function permissions() {
        return $this->hasMany('App\Permission');
    }
}
