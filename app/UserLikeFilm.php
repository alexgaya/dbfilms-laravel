<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLikeFilm extends Model
{
    protected $table = 'User_like_film';
    protected $fillable = [
        'user_id', 'film_id'
    ];
    protected $primaryKey = ['user_id', 'film_id'];
    public $incrementing = false;
    public $timestamps = false;
    
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        if (is_array($this->primaryKey)) {
            foreach ($this->primaryKey as $pk) {
                $query->where($pk, '=', $this->original[$pk]);
            }
            return $query;
        } else {
            return parent::setKeysForSaveQuery($query);
        }
    }
}
