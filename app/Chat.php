<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "chats";
    protected $fillable = [
        'match_id','user_id','body'
    ];
    public function User(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
