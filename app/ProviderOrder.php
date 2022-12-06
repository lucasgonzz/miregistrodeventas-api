<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderOrder extends Model
{
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('articles', 'provider', 'provider_order_afip_tickets');
    }

    function provider_order_afip_tickets() {
        return $this->hasMany('App\ProviderOrderAfipTicket');
    }

    function current_acount() {
        return $this->hasOne('App\CurrentAcount');
    }

    function provider() {
        return $this->belongsTo('App\Provider');
    }

    function articles() {
        return $this->belongsToMany('App\Article')->withPivot('amount', 'cost', 'notes', 'received', 'iva_id', 'received_cost', 'update_cost');
    }
}
