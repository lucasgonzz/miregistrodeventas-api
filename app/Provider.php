<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Provider extends Model
{
    use Notifiable;
    
    protected $guarded = [];
}
