<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class people extends Model
{
    //

    use SoftDeletes;
    protected $guarded=[];
}
