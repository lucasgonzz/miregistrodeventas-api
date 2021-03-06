<?php

namespace App;

// use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use ChristianKuri\LaravelFavorite\Traits\Favoriteability;

class Buyer extends Model
{
    use Notifiable;
    // use Favoriteability;
    
    protected $fillable = ['name', 'surname', 'address', 'city', 'email', 'password'];

    function notifications() {
    	return $this->hasMany('app\Notification');
    }
}
