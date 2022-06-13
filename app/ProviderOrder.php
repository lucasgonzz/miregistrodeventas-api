<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderOrder extends Model
{
    protected $guarded = [];

    function provider() {
        return $this->belongsTo('App\Provider');
    }

    function articles() {
        return $this->belongsToMany('App\Article')->withPivot('amount', 'notes', 'received');
    }
}
