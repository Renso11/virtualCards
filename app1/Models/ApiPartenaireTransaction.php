<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiPartenaireTransaction extends Model
{
   use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    
    public function apiPartenaireAccount(){
        return $this->belongsTo('App\Models\ApiPartenaireAccount');
    }
}
