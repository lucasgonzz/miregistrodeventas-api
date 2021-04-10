<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $guarded = [];

    public function sellers() {
    	return $this->hasMany('App\Seller');
    }

    public function seller() {
    	return $this->belongsTo('App\Seller');
    }

    public function clients() {
    	return $this->hasMany('App\Client');
    }
}
