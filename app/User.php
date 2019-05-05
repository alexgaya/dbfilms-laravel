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
        'nick', 'email', 'password', 'description'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    protected $table = 'User';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /*protected $casts = [
        'email_verified_at' => 'datetime',
    ];*/
    
    // Relación One to Many
    public function film() {
        return $this->hasMany('App\Film');
    }
    
    public function chapter() {
        return $this->hasMany('App\Chapter');
    }
    
    public function serie() {
        return $this->hasMany('App\Serie');
    }
    
    public function favouriteFilms() {
        return $this->belongsToMany('App\Film', 'User_film', 'user_id', 'film_id')->where('favourite', 1);
    }
    
    public function likedFilms() {
        return $this->belongsToMany('App\Film', 'User_film', 'user_id', 'film_id')->where('like', 1);
    }
    
    public function seenFilms() {
        return $this->belongsToMany('App\Film', 'User_film', 'user_id', 'film_id')->where('seen', 1);
    }
    
    public function pendingFilms() {
        return $this->belongsToMany('App\Film', 'User_film', 'user_id', 'film_id')->where('pending', 1);
    }
    
//    public function seen() {
//        return $this->belongsToMany('App\Post', 'user_seen_post');
//    }
//    
//    public function favourite() {
//        return $this->belongsToMany('App\Post', 'user_favourite_post');
//    }
//    
//    public function pending() {
//        return $this->belongsToMany('App\Post', 'user_pending_post');
//    }
//    
//    public function seeing() {
//        return $this->belongsToMany('App\Post', 'user_seeing_post');
//    }
    
    
}
