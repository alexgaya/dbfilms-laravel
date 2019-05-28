<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lists extends Model
{
    protected $table = 'List';
    
    protected $fillable = [
        'name', 'description'
    ];
    
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function films() {
        return $this->belongsToMany('App\Film', 'List_has_Film', 'list_id', 'film_id');
    }
    
    public function series() {
        return $this->belongsToMany('App\Serie', 'List_has_Serie', 'list_id', 'serie_id');
    }
    
    public function filmsLimited() {
        return $this->belongsToMany('App\Film', 'List_has_Film', 'list_id', 'film_id')->select(['id', 'image'])->take(3);
    }
    
    public function seriesLimited() {
        return $this->belongsToMany('App\Serie', 'List_has_Serie', 'list_id', 'serie_id')->select(['id', 'image'])->take(2);
    }
}
