<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfRetrait extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    
    public function userClient(){
        return $this->belongsTo('App\Models\UserClient');
    }

    public function userCard(){
        return $this->belongsTo('App\Models\UserCard');
    }
}
