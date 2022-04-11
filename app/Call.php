<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    function buyer() {
        return $this->belongsTo('App\Buyer');
    }
}
