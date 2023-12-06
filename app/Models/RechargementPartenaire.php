<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechargementPartenaire extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 

    public function partenaire(){
        return $this->belongsTo('App\Models\Partenaire');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
