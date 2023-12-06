<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gamme extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    
    public function cartesEnStock(){
        return $this->hasMany('App\Models\CartePhysique')->where('status',1);
    }
}
