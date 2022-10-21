<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    
    protected $guarded = [];

    public function impressions() {
        return $this->hasMany('App\Impression');
    }

    function scopeWithAll($query) {
        $query->with('client.iva_condition', 'client.price_type', 'buyer', 'articles', 'impressions', 'special_price', 'commissions', 'discounts', 'afip_ticket', 'combos', 'order.cupon', 'services', 'employee', 'budget.articles', 'budget.client');
    }

    public function budget() {
        return $this->belongsTo('App\Budget');
    }

    public function sale_type() {
        return $this->belongsTo('App\SaleType');
    }

    public function afip_ticket() {
        return $this->hasOne('App\AfipTicket');
    }

    public function commissioners() {
        return $this->belongsToMany('App\Commissioner')->withPivot('percentage', 'is_seller');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function employee() {
        return $this->belongsTo('App\User', 'employee_id');
    }

    public function current_acounts() {
        return $this->hasMany('App\CurrentAcount');
    }

    public function commissions() {
        return $this->hasMany('App\Commission');
    }

    public function order() {
        return $this->belongsTo('App\Order');
    }

    public function discounts() {
        return $this->belongsToMany('App\Discount')->withPivot('percentage');
    }

    public function articles() {
        return $this->belongsToMany('App\Article')->withPivot('amount', 'cost', 'price', 'discount', 'with_dolar');
    }

    public function combos() {
        return $this->belongsToMany('App\Combo')->withPivot('amount', 'price',);
    }

    public function services() {
        return $this->belongsToMany('App\Service')->withPivot('discount', 'amount', 'price');
    }

    public function client() {
        return $this->belongsTo('App\Client');
    }

    public function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    public function special_price() {
        return $this->belongsTo('App\SpecialPrice');
    }
}
