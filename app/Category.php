<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "categories";
    protected $fillable = [
        'name','description'
    ];
    public function Rooms(){
        return $this->hasMany('App\Room', 'category_id');
    }

}
