<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commissioner extends Model
{
	protected $guarded = [];
	
    public function sales() {
        return $this->belongsToMany('App\Sales')->withPivot('percentage');
    }
	
    public function seller() {
        return $this->belongsTo('App\Seller');
    }
}
