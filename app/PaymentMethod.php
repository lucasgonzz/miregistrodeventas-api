<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $guarded = [];

    function type() {
        return $this->belongsTo('App\PaymentMethodType', 'payment_method_type_id');
    }

    function credential() {
        return $this->hasOne('App\Credential');
    }
}
