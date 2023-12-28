<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiaire extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    
    public function bcvBeneficiaries(){
        return $this->hasMany('App\Models\BeneficiaireBcv')->where('deleted',0);
    }
    
    public function cardBeneficiaries(){
        return $this->hasMany('App\Models\BeneficiaireCard')->where('deleted',0);
    }
    
    public function momoBeneficiaries(){
        return $this->hasMany('App\Models\BeneficiaireMomo')->where('deleted',0);
    }
}
