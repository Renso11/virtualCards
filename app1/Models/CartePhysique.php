<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartePhysique extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    public function gamme(){
        return $this->belongsTo('App\Models\Gamme');
    }
}
