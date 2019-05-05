<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    protected $table = 'Film';
    
    protected $fillable = [
        'name', 'description', 'image'. 'duration', 'rating'
    ];
    
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    
    
}
