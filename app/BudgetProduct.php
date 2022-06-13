<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BudgetProduct extends Model
{
    protected $guarded = [];

    function article_stocks() {
        return $this->hasMany('App\BudgetProductArticleStock');
    }

    function deliveries() {
        return $this->hasMany('App\BudgetProductDelivery');
    }
}
