<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduction extends Model
{
    protected $guarded = [];

    protected $dates = ['start_at', 'finish_at'];

    function scopeWithAll($query) {
        // $query->with('articles', 'order_production_status', 'client.comercio_city_user');
        $query->with('articles.recipe.articles', 'articles_finished', 'order_production_status', 'client.comercio_city_user');
    }

    function budget() {
        return $this->belongsTo('App\Budget', 'budget_id');
    }

    function sale() {
        return $this->hasOne('App\Sale');
    }
    
    function client() {
        return $this->belongsTo('App\Client');
    }

    function articles() {
        return $this->belongsToMany('App\Article')->withPivot('price', 'amount', 'bonus', 'location', 'delivered', 'employee_id');
    }

    function articles_finished() {
        return $this->belongsToMany('App\Article', 'article_order_production_finished', 'article_id', 'order_production_id')->withPivot('order_production_status_id', 'amount');
    }

    function order_production_status() {
        return $this->belongsTo('App\OrderProductionStatus');
    }
}
