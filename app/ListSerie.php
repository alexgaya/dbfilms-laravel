<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListSerie extends Model {

    protected $table = 'List_has_Serie';
    protected $fillable = [
        'list_id', 'serie_id'
    ];
    protected $primaryKey = ['list_id', 'serie_id'];
    public $incrementing = false;
    public $timestamps = false;

    /**
     * Set the keys for a save update query.
     * This is a fix for tables with composite keys
     * TODO: Investigate this later on
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
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
