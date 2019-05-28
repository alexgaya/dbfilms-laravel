<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'Comment';
    
    protected $fillable = [
        'content', 'film_id', 'serie_id', 'chapter_id', 'user_id'
    ];
    
    public function film(){
        return $this->belongsTo('App\Film', 'film_id');
    }
    
    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id')->select('id', 'nick', 'image');
    }
}
