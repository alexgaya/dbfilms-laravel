<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    protected $table = 'Film';
    
    public function setSeenAttribute($value){
        $this->attributes['seen'] = $value;
    }
    
    protected $fillable = [
        'name', 'description', 'image'. 'duration', 'rating', 'trailer'
    ];
    
    public function user() {
        return $this->belongsTo('App\User', 'user_id')->select(['id', 'nick', 'image']);
    }
    
    public function link() {
        return $this->hasMany('App\Link', 'film_id', 'id')->select(['id', 'user_id', 'film_id', 'language_id', 'url']);
    }
    
    public function genre() {
        return $this->belongsToMany('App\Genre', 'Genre_has_Film', 'film_id', 'genre_id');
    }
    
    public function comment() {
        return $this->hasMany('App\Comment', 'film_id')->select(['id', 'film_id', 'content', 'created_at', 'updated_at', 'user_id'])->latest();
    }
    
}
