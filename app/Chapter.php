<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $table = 'Chapter';
    
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
}
