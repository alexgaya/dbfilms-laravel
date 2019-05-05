<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListFilm extends Model {

    protected $table = 'List_has_Film';
    protected $fillable = [
        'list_id', 'film_id'
    ];
    protected $primaryKey = ['list_id', 'film_id'];
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
