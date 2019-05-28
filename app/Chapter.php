<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $table = 'Chapter';
    
    protected $fillable = ['user_id', 'serie_id', 'name', 'description', 'duration', 'seasson'];


    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function serie() {
        return $this->belongsTo('App\Serie', 'serie_id');
    }
    
    public function link() {
        return $this->hasMany('App\Link', 'chapter_id', 'id')->select(['id', 'user_id', 'chapter_id', 'language_id', 'url']);
    }
    
    public function comment() {
        return $this->hasMany('App\Comment', 'chapter_id')->select(['id', 'chapter_id', 'content', 'created_at', 'updated_at', 'user_id'])->latest();
    }
}
