<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $table = 'Follow';
    protected $fillable = [
        'user_id', 'user_followed'
    ];
    protected $primaryKey = [
        'user_id', 'user_followed'
    ];
    
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
    
    public function userFollowed(){
        return $this->hasOne('App\User', 'id', 'user_followed')->select('id', 'nick', 'image');
    }
    
    public function userFollowMe(){
        return $this->hasOne('App\User', 'id', 'user_id')->select('id', 'nick', 'image');
    }
}
