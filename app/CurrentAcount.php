<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrentAcount extends Model
{
    protected $guarded = [];

    public function sale() {
    	return $this->belongsTo('App\Sale');
    }

    public function client() {
    	return $this->belongsTo('App\Client');
    }

    public function seller() {
    	return $this->belongsTo('App\Seller');
    }
}
