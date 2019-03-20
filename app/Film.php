<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    protected $table = 'films';
    
    // Relación Many to One
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }
}
