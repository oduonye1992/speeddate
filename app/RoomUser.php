<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomUser extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "room_user";
    protected $fillable = [
        'user_id', 'room_id'
    ];
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
    public function room(){
        return $this->belongsTo('App\Room', 'room_id');
    }
}
