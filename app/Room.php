<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "rooms";
    protected $fillable = [
        'title', 'description', 'start_time', 'end_time', 'creator_id', 'image'
    ];
    public function creator(){
        return $this->belongsTo('App\User', 'creator_id');
    }
}
