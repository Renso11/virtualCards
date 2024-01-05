<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouchardPartenaire extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 
    public function userPartenaire(){
        return $this->belongsTo('App\Models\UserPartenaire');
    }
}
