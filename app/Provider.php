<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Provider extends Model
{
    use Notifiable;
    
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('iva_condition', 'comercio_city_user', 'provider_price_lists')
            ->withCount('current_acounts');
    }

    function provider_price_lists() {
        return $this->hasMany('App\ProviderPriceList');
    }

    function comercio_city_user() {
        return $this->belongsTo('App\User', 'comercio_city_user_id');
    }

    public function iva_condition() {
        return $this->belongsTo('App\IvaCondition');
    }

    public function current_acounts() {
        return $this->hasMany('App\CurrentAcount');
    }
}
