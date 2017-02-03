<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "profiles";
    protected $fillable = [
        'user_id', 'bio', 'birthdate', 'gender', 'image', 'phone'
    ];
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
