<?php


namespace App\Models\BaseModel;

use Illuminate\Foundation\Auth\User as Authenticatable;

class TenantModel extends Authenticatable
{
    protected $connection = 'tenant';
}
