<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuperBudgetFeature extends Model
{
    protected $guarded = [];

    function super_budget_feature_items() {
        return $this->hasMany('App\SuperBudgetFeatureItem');
    }
}
