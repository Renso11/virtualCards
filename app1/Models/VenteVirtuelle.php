<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenteVirtuelle extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 

    public function kycClient(){
        return $this->belongsTo('App\Models\KycClient');
    }
    public function gamme(){
        return $this->belongsTo('App\Models\Gamme');
    }
    public function cartePhysique(){
        return $this->belongsTo('App\Models\CartePhysique');
    }
}
