<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;
    protected $guarded = [];public $incrementing = false; 

    public function permission(){
        return $this->belongsTo('App\Models\Permission')->where('deleted',0);
    }
}
