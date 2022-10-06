<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Provider extends Model
{
    use Notifiable;
    
    protected $guarded = [];

    function scopeWithAll($query) {
        $query->with('iva_condition')
            ->withCount('current_acounts');
    }

    public function iva_condition() {
        return $this->belongsTo('App\IvaCondition');
    }

    public function current_acounts() {
        return $this->hasMany('App\CurrentAcount');
    }
}
