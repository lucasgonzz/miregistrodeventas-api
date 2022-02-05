<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    
    protected $guarded = [];

    function views() {
        return $this->morphMany('App\View', 'viewable');
    }

    function prices_lists() {
        return $this->belongsToMany('App\PricesList');
    }

    function brand() {
        return $this->belongsTo('App\Brand');
    }

    function descriptions() {
        return $this->hasMany('App\Description');
    }

    function tags() {
        return $this->belongsToMany('App\Tag');
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

    public function specialPrices() {
        return $this->belongsToMany('App\SpecialPrice')->withPivot('price');
    }
    
    public function providers(){
        return $this->belongsToMany('App\Provider')
                                                    ->withPivot('amount', 'cost', 'price')
                                                    ->withTimestamps()
                                                    ->orderBy('id', 'DESC');
    }

    public function questions() {
        return $this->hasMany('App\Question');
    }
}
