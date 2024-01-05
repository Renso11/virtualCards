<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCommission extends Model
{
   use HasFactory;
    protected $guarded = [];public $incrementing = false; 

    public function accountCommissionOperations(){
        return $this->hasMany('App\Models\AccountCommissionOperation');
    }
}
