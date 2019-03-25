<?php

namespace App;

use Illuminate\Notifications\Notifiable;
//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'description'
    ];

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
    /*protected $casts = [
        'email_verified_at' => 'datetime',
    ];*/
    
    // Relación One to Many
    public function posts() {
        return $this->hasMany('App\Post');
    }
    
    public function seen() {
        return $this->belongsToMany('App\Post', 'user_seen_post');
    }
    
    public function favourite() {
        return $this->belongsToMany('App\Post', 'user_favourite_post');
    }
    
    public function pending() {
        return $this->belongsToMany('App\Post', 'user_pending_post');
    }
    
    public function seeing() {
        return $this->belongsToMany('App\Post', 'user_seeing_post');
    }
    
    
}
