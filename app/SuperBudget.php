<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuperBudget extends Model
{
    protected $guarded = [];
    protected $dates = ['offer_validity'];

    function scopeWithAll($query) {
        $query->with('super_budget_features');
    }

    function super_budget_features() {
        return $this->hasMany('App\SuperBudgetFeature');
    }
}
