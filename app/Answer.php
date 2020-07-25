<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['text', 'buyer_id', 'user_id', 'article_id'];

    public function consultation() {
    	return $this->belongsTo('App\Consultation');
    }

    public function article() {
    	return $this->belongsTo('App\Answer');
    }
}
