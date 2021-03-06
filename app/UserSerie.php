<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSerie extends Model
{
    protected $table = 'User_serie';
    protected $fillable = [
        'user_id', 'serie_id', 'like', 'favourite', 'pending', 'seen'
    ];
    protected $primaryKey = ['user_id', 'serie_id'];
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
