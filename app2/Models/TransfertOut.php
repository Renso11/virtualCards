<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfertOut extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 

    public function userCard(){
        return $this->belongsTo('App\Models\UserCard');
    }

    public function userClient(){
        return $this->belongsTo('App\Models\UserClient');
    }

    public function receveur(){
        return $this->belongsTo('App\Models\UserClient');
    }

    public function transferIns(){
        return $this->hasMany('App\Models\TransfertIn');
    }
}
