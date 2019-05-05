<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    protected $table = 'Serie';
    
    protected $fillable = [
        'name', 'description', 'image', 'duration', 'rating'
    ];  
    
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function chapters() {
        return $this->hasMany('App\Chapter');
    }
}
