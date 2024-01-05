<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePartenaire extends Model
{
    use HasFactory;

    protected $guarded = [];public $incrementing = false; 
    public function rolePartenairePermissions(){
        return $this->hasMany('App\Models\RolePartenairePermission')->where('deleted', 0);
    }
}
