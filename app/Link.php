<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'Link';
    
    protected $fillable = [
        'url', 'language_id', 'chapter_id', 'film_id'
    ];  
    
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function film() {
        return $this->belongsTo('App\Film', 'film_id');
    }
    
    public function chapter() {
        return $this->belongsTo('App\Chapter', 'chapter_id');
    }
}
