<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrivMessage extends Model {

    protected $table = 'PrivateMessage';
    protected $fillable = ['sender_id', 'receiver_id', 'text', 'read'];

    public function senderUser() {
        return $this->belongsTo('App\User', 'sender_id', 'id')->select(['id','nick', 'image']);
    }

}
