<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficiaireCard extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    
    public function beneficiaire(){
        return $this->hasOne('App\Models\ApiPartenaireAccount');
    }
}
