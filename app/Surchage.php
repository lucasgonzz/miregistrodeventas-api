<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Surchage extends Model
{
    use SoftDeletes;

    protected $guarded = [];
}
