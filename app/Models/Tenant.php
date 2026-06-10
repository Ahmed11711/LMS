<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenants';

    public function subscription()
    {

        return $this->hasOne(UserPackage::class, 'user_id', 'user_id');
    }
}
