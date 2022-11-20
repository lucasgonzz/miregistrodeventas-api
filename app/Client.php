<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use Notifiable;
    
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('sales', 'iva_condition', 'price_type', 'location', 'comercio_city_user', 'buyer')
            ->withCount('current_acounts');
    }
    
    public function sales() {
        return $this->hasMany('App\Sale');
    }
    
    public function buyer() {
        return $this->hasOne('App\Buyer', 'comercio_city_client_id');
    }
    
    public function comercio_city_user() {
        return $this->belongsTo('App\User', 'comercio_city_user_id');
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
    
    public function price_type() {
        return $this->belongsTo('App\PriceType');
    }
    
    public function location() {
        return $this->belongsTo('App\Location');
    }
    
    // public function errors() {
    //     return $this->hasMany('App\Hola');
    // }
}
