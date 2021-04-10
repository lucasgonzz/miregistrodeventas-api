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
    
    protected $guarded = [];

    public function routeNotificationForWhatsApp()
	{
		return $this->phone;
	}
}
