<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "rooms";
    protected $fillable = [
        'title', 'description', 'category_id', 'start_time', 'end_time', 'creator_id', 'image', 'description'
    ];
    public function creator(){
        return $this->belongsTo('App\User', 'creator_id');
    }
    public function category(){
        return $this->belongsTo('App\Category', 'creator_id');
    }
}
