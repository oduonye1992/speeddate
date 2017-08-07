<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "matches";
    protected $fillable = [
        'user_id', 'matcher_id'
    ];
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
    public function matcher(){
        return $this->belongsTo('App\User', 'matcher_id');
    }
}
