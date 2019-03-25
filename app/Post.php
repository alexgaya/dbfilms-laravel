<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    
    
    // Relación Many to One
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }

        
    public function seen() {
        return $this->belongsToMany('App\User', 'user_seen_post');
    }
    
    public function favourite() {
        return $this->belongsToMany('App\User', 'user_favourite_post');
    }
    
    public function pending() {
        return $this->belongsToMany('App\User', 'user_pending_post');
    }
    
    public function seeing() {
        return $this->belongsToMany('App\User', 'user_seeing_post');
    }
}
