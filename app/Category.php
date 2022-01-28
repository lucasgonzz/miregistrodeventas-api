<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
	public $timestamps = false;

	protected $fillable = ['name', 'user_id', 'icon_id'];	

	function icon() {
		return $this->belongsTo('App\Icon');
	}
}
