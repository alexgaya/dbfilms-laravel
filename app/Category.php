<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    
    
    // Relación One to Many
    public function films() {
        return $this->hasMany('App\Film');
    }
}
