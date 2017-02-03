<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomSettings extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "room_settings";
    protected $fillable = [
        'room_id', 'switch_interval'
    ];
    public function room(){
        return $this->belongsTo('App\Room', 'room_id');
    }
}
