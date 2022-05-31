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
    
    public function iva_condition() {
        return $this->belongsTo('App\IvaCondition');
    }
    
    public function current_acounts() {
        return $this->hasMany('App\CurrentAcount');
    }
    
    // public function errors() {
    //     return $this->hasMany('App\Hola');
    // }
}
