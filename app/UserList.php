<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserList extends Model
{
    protected $table = 'User_list';
    protected $fillable = [
        'user_id', 'list_id', 'follow'
    ];
    protected $primaryKey = ['user_id', 'list_id'];
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
