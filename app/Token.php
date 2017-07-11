<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "tokens";
    protected $fillable = [
        'user_id', 'token'
    ];
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
