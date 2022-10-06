<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduction extends Model
{
    protected $guarded = [];

    protected $dates = ['start_at', 'finish_at'];

    function scopeWithAll($query) {
        $query->with('articles', 'order_production_status', 'client');
    }

    function budget() {
        return $this->belongsTo('App\Budget', 'budget_id');
    }
    
    function client() {
        return $this->belongsTo('App\Client');
    }

    function articles() {
        return $this->belongsToMany('App\Article')->withPivot('price', 'amount', 'bonus', 'location', 'delivered');
    }

    function order_production_status() {
        return $this->belongsTo('App\OrderProductionStatus');
    }
}
