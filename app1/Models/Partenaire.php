<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    
    public function accountDistribution(){
        return $this->hasOne('App\Models\AccountDistribution');
    }
    
    public function accountCommission(){
        return $this->hasOne('App\Models\AccountCommission');
    }
    
    public function accountVente(){
        return $this->hasOne('App\Models\AccountVente');
    }
}
