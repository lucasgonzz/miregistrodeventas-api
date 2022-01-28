<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $guarded = [];

    public function client() {
        return $this->belongsTo('App\Client');
    }
}
