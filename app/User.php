<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = false;
    protected $guarded = [];
    protected $dates = ['expired_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function plan() {
        return $this->belongsTo('App\Plan');
    }

    public function permissions() {
        return $this->belongsToMany('App\Permission');
    }

    public function articles() {
        return $this->hasMany('App\Article');
    }

    function sale_types() {
        return $this->hasMany('App\SaleType');
    }

    public function addresses() {
        return $this->hasMany('App\Address');
    }

    public function subscription() {
        return $this->hasOne('App\Subscription');
    }

    public function articles_sub_user() {
        return $this->hasMany('App\Article', 'sub_user_id');
    }

    public function employees() {
        return $this->hasMany('App\User', 'owner_id');
    }

    public function schedules() {
        return $this->hasMany('App\Schedule');
    }

    public function collections() {
        $status = Auth()->user()->status;
        if ($status == 'admin' || $status == 'super') {
            return $this->hasMany('App\Collection', 'admin_id');
        } else {
            return $this->hasMany('App\Collection', 'commerce_id');
        }
    }

    public function owner() {
        return $this->belongsTo('App\User', 'id');  
    }

    public function admin() {
        return $this->belongsTo('App\User', 'id');  
    }

    public function commerces() {
        return $this->hasMany('App\User', 'admin_id');
    }

    public function questions() {
        return $this->hasMany('App\Question');
    }

    public function workdays() {
        return $this->belongsToMany('App\Workday');
    }
}
