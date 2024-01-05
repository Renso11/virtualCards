<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserClient extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    
    protected $guarded = [];public $incrementing = false; 
    
    public function role(){
        return $this->belongsTo('App\Models\Role');
    }
    
    public function kycClient(){
        return $this->belongsTo('App\Models\KycClient');
    }
    
    public function userCards(){
        return $this->hasMany('App\Models\UserCard');
    }
    
    public function userCard(){
        return $this->hasMany('App\Models\UserCard')->where('is_first',1);
    }
    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }    
}
