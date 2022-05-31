<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AfipInformation extends Model
{
    protected $guarded = [];

    protected $dates = ['inicio_actividades'];

    public function iva_condition() {
        return $this->belongsTo('App\IvaCondition');
    }
}
