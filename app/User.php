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
        'nick', 'email', 'password', 'description', 'banned', 'status', 'hidden', 'perms'
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
        //return $this->belongsToMany('App\Film', 'User_film', 'user_id', 'film_id')->select(['user_id','id','name','image','seen'])->where('seen', 1)->paginate(12);
        return $this->belongsToMany('App\Film', 'User_film', 'user_id', 'film_id')->select(['id', 'name', 'image'])->where('seen', 1);
    }
    
    public function pendingFilms() {
        return $this->belongsToMany('App\Film', 'User_film', 'user_id', 'film_id')->where('pending', 1);
    }
    
    public function unReadMessages() {
        return $this->hasMany('App\PrivMessage', 'receiver_id')->where('read', 0);
    }
    
    public function readMessages() {
        return $this->hasMany('App\PrivMessage', 'receiver_id')->where('read', 1);
    }
}
