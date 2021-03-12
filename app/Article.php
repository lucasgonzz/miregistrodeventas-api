<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    
    protected $guarded = [];

    function views() {
        return $this->morphMany('App\View', 'viewable');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function category() {
        return $this->belongsTo('App\Category');
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
        return $this->belongsToMany('App\SpecialPrice')->withPivot('price');;
    }
    
    public function providers(){
        return $this->belongsToMany('App\Provider')
                                                    ->withPivot('amount', 'cost', 'price')
                                                    ->withTimestamps()
                                                    ->orderBy('id', 'DESC');
    }
}
