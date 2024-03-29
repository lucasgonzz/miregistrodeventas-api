<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    public function article() {
        return $this->belongsTo('App\Article');
    	// return $this->belongsTo('App\Article')->withPivot('variant_id');
    }

    public function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function answer() {
    	return $this->hasOne('App\Answer');
    }
}
