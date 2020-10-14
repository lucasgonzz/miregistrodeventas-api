<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    
    protected $guarded = [];

    public function articles() {
        return $this->belongsToMany('App\Article')->withPivot('amount', 'measurement', 'cost', 'price');
    }

    public function client() {
        return $this->belongsTo('App\Client');
    }

    public function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    public function specialPrice() {
        return $this->belongsTo('App\SpecialPrice');
    }
}
