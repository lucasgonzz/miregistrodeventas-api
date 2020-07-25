<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = ['text', 'buyer_id', 'user_id'];

    public function answer() {
    	return $this->hasOne('App\Answer');
    }

    public function article() {
    	return $this->belongsTo('App\Article');
    }

    public function user() {
    	return $this->belongsTo('App\User');
    }
}
