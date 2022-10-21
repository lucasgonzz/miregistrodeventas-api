<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrentAcount extends Model
{
    protected $guarded = [];

    public function sale() {
        return $this->belongsTo('App\Sale');
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

    public function payment_method() {
        return $this->belongsTo('App\CurrentAcountPaymentMethod', 'current_acount_payment_method_id');
    }

    public function client() {
        return $this->belongsTo('App\Client');
    }

    public function seller() {
    	return $this->belongsTo('App\Seller');
    }
}
