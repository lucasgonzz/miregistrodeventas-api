<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('credit_card_payment_plans');
    }

    function credit_card_payment_plans() {
        return $this->hasMany('App\CreditCardPaymentPlan');
    }
}
