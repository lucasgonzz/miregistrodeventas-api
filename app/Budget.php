<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $guarded = [];

    protected $dates = ['start_at', 'finish_at'];

    function client() {
        return $this->belongsTo('App\Client');
    }

    function observations() {
        return $this->hasMany('App\BudgetObservation');
    }

    function products() {
        return $this->hasMany('App\BudgetProduct');
    }

    function order_production() {
        return $this->hasOne('App\OrderProduction');
    }

}
