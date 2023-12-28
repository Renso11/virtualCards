<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfertIn extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 

    public function userCard(){
        return $this->belongsTo('App\Models\UserCard');
    }

    public function userClient(){
        return $this->belongsTo('App\Models\UserClient');
    }

    public function transfertOut(){
        return $this->belongsTo('App\Models\TransfertOut');
    }
}
