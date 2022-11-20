<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrentAcount extends Model
{
    protected $guarded = [];

    public function sale() {
        return $this->belongsTo('App\Sale');
    }

    public function articles() {
        return $this->belongsToMany('App\Article')->withPivot('amount', 'price');
    }

    public function budget() {
        return $this->belongsTo('App\Budget');
    }

    public function order_production() {
        return $this->belongsTo('App\OrderProduction');
    }

    public function provider_order() {
        return $this->belongsTo('App\ProviderOrder');
    }

    public function checks() {
        return $this->hasMany('App\Check');
    }

    public function current_acount_payment_methods() {
        return $this->belongsToMany('App\CurrentAcountPaymentMethod')->withPivot('amount', 'bank', 'num', 'payment_date', 'credit_card_id', 'credit_card_payment_plan_id');
    }

    public function client() {
        return $this->belongsTo('App\Client');
    }

    public function seller() {
    	return $this->belongsTo('App\Seller');
    }
}
