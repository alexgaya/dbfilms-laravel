<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    
    
    // Relación One to Many
    public function posts() {
        return $this->hasMany('App\Post');
    }
}
