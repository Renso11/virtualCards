<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retrait extends Model
{
    use HasFactory;

    protected $guarded = [];public $incrementing = false; 

    public function partenaire(){
        return $this->belongsTo('App\Models\Partenaire');
    }

    public function userPartenaire(){
        return $this->belongsTo('App\Models\UserPartenaire');
    }
    
    public function userClient(){
        return $this->belongsTo('App\Models\UserClient');
    }

    public function userCard(){
        return $this->belongsTo('App\Models\UserCard');
    }
}
