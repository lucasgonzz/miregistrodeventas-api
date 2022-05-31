<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduction extends Model
{
    protected $guarded = [];

    function budget() {
        return $this->belongsTo('App\Budget', 'budget_id');
    }

    function status() {
        return $this->belongsTo('App\OrderProductionStatus', 'order_production_status_id');
    }
}
