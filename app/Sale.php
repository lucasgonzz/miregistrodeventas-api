<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    
    protected $guarded = [];

    public function impressions() {
        return $this->hasMany('App\Impression');
    }

    public function sale_type() {
        return $this->belongsTo('App\SaleType');
    }

    public function commissioners() {
        return $this->belongsToMany('App\Commissioner')->withPivot('percentage', 'is_seller');
    }

    public function current_acounts() {
        return $this->hasMany('App\CurrentAcount');
    }

    public function commissions() {
        return $this->hasMany('App\Commission');
    }

    public function discounts() {
        return $this->belongsToMany('App\Discount')->withPivot('percentage');
    }

    public function articles() {
        return $this->belongsToMany('App\Article')->withPivot('amount', 'cost', 'price');
    }

    public function client() {
        return $this->belongsTo('App\Client');
    }

    public function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    public function specialPrice() {
        return $this->belongsTo('App\SpecialPrice');
    }
}
