<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frai extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 

    public function fraiCompteCommissions(){
        return $this->hasMany('App\Models\FraiCompteCommission')->where('deleted',0);
    }
}
