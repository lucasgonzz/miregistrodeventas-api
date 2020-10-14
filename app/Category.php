<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
	public $timestamps = false;

	protected $fillable = ['name', 'user_id'];	

    function views() {
        return $this->morphMany('App\View', 'viewable');
    }
    
    public function articles() {
        return $this->hasMany('App\Article');
    }
}
