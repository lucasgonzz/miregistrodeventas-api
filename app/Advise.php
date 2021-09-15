<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advise extends Model
{
    protected $guarded = [];

    public function buyer() {
        return $this->belongsTo('App\Buyer');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function article() {
        return $this->belongsTo('App\Article');
    }
}
