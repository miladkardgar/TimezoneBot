<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class user_notice extends Model
{
    //
    protected $guarded=[];
    use SoftDeletes;

    public function userInfo()
    {
        return $this->hasOne('App\people','id','user_id');
    }
}
