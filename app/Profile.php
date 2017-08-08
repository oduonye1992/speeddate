<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model {
    protected $dates = ['deleted_at'];
    protected $table = "profiles";
    protected $fillable = [
        'user_id', 'bio', 'birthdate', 'gender', 'image', 'phone',
        'address', 'state', 'country','email'
    ];
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
