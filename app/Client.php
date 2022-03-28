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
    
    public function iva() {
        return $this->belongsTo('App\Iva');
    }
    
    public function current_acounts() {
        return $this->hasMany('App\CurrentAcount');
    }
}
