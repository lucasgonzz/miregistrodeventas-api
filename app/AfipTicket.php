<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AfipTicket extends Model
{
    protected $guarded = [];
    protected $dates = ['cae_expired_at'];
}
