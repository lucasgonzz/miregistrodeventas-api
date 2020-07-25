<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
    	'admin_id', 'commerce_id', 'collected_months', 
    	'collected_per_month', 'delivered'
    ];

    public function commerce() {
        return $this->belongsTo('App\User', 'commerce_id');
    }
}
