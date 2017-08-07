<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class States extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "states";
    protected $fillable = [
        'title', 'code'
    ];
}
