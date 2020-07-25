<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use ChristianKuri\LaravelFavorite\Traits\Favoriteability;

class Buyer extends Authenticatable
{
    use Notifiable;
    use Favoriteability;
    
    protected $fillable = ['name', 'surname', 'address', 'city', 'email', 'password'];
}
