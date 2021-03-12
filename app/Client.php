<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    
    protected $guarded = [];
    
    public function sales() {
        return $this->hasMany('App\Sale');
    }
    
    public function seller() {
        return $this->belongsTo('App\Seller');
    }
    
    public function current_acounts() {
        return $this->hasMany('App\CurrentAcount');
    }
}
