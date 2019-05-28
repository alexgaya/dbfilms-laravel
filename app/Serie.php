<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $table = 'Serie';
    
    public function setSeenAttribute($value){
        $this->attributes['seen'] = $value;
    }
    
    protected $fillable = [
        'name', 'description', 'image', 'duration', 'rating', 'trailer'
    ];  
    
    public function user() {
        return $this->belongsTo('App\User', 'user_id')->select(['id', 'nick', 'image']);
    }
    
    public function chapters() {
        return $this->hasMany('App\Chapter')->orderBy('id_ep', 'asc');
    }
    
    public function genre() {
        return $this->belongsToMany('App\Genre', 'Genre_has_Serie', 'serie_id', 'genre_id');
    }
    
    public function comment() {
        return $this->hasMany('App\Comment', 'serie_id')->select(['id', 'serie_id', 'content', 'created_at', 'updated_at', 'user_id'])->latest();
    }
}
