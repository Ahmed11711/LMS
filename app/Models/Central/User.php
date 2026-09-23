<?php

namespace App\Models\Central;

use App\Casts\StorageUrlCast;
use App\Models\Tenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;


    protected $connection = 'LMS_CENTER'; // Central DB
    protected $table = 'users';

    // protected $fillable = ['name', 'email', 'password', 'role', 'phone'];
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'profile_image' => StorageUrlCast::class,
    ];

    // JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'name', 'username');
    }
    public function packages()
    {
        return $this->hasMany(\App\Models\Central\UserPackage::class, 'user_id');
    }

    public function activePackage()
    {
        return $this->hasOne(\App\Models\Central\UserPackage::class, 'user_id')
            ->where('status', 'active')
            ->latestOfMany();
    }
}
