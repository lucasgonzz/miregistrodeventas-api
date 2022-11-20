<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('images.color', 'iva', 'sizes', 'colors', 'condition', 'descriptions', 'sub_category', 'variants', 'tags', 'brand', 'discounts', 'specialPrices', 'providers', 'provider_price_list', 'deposits');
    }

    function views() {
        return $this->morphMany('App\View', 'viewable');
    }

    function deposits() {
        return $this->belongsToMany('App\Deposit')->withPivot('value');
    }

    function prices_lists() {
        return $this->belongsToMany('App\PricesList');
    }

    function provider_price_list() {
        return $this->belongsTo('App\ProviderPriceList');
    }

    function recipe() {
        return $this->hasOne('App\Recipe');
    }

    function discounts() {
        return $this->hasMany('App\ArticleDiscount');
    }

    function combos() {
        return $this->belongsToMany('App\Article');
    }

    function brand() {
        return $this->belongsTo('App\Brand');
    }

    function iva() {
        return $this->belongsTo('App\Iva');
    }

    function descriptions() {
        return $this->hasMany('App\Description');
    }

    function tags() {
        return $this->belongsToMany('App\Tag');
    }

    function sizes() {
        return $this->belongsToMany('App\Size');
    }

    function colors() {
        return $this->belongsToMany('App\Color');
    }

    function condition() {
        return $this->belongsTo('App\Condition');
    }

    function variants() {
        return $this->hasMany('App\Variant');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function sub_category() {
        return $this->belongsTo('App\SubCategory');
    }

    public function marker() {
        return $this->hasOne('App\Marker');
    }

    public function images() {
        return $this->hasMany('App\Image', 'article_id');
    }

    public function sub_user() {
        return $this->belongsTo('App\User', 'sub_user_id');
    }
    
    public function updated_by() {
        return $this->belongsTo('App\User', 'updated_by', 'id');
    }

    public function sales() {
        return $this->belongsToMany('App\Sale')->latest();
    }

    public function budgets() {
        return $this->belongsToMany('App\Budget')->latest();
    }

    public function order_productions() {
        return $this->belongsToMany('App\OrderProduction')->latest();
    }

    public function provider_orders() {
        return $this->belongsToMany('App\ProviderOrder')->latest();
    }

    public function recipes() {
        return $this->belongsToMany('App\Recipe')->latest();
    }

    public function specialPrices() {
        return $this->belongsToMany('App\SpecialPrice')->withPivot('price');
    }
    
    public function providers(){
        return $this->belongsToMany('App\Provider')->withPivot('amount', 'cost', 'price')
                                                    ->withTimestamps();
    }

    public function questions() {
        return $this->hasMany('App\Question');
    }
}
