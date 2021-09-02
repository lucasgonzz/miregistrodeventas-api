<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advise extends Model
{
    protected $guarded = [];

    public function buyer() {
        return $this->belongsTo('App\Buyer');
    }
    
}
