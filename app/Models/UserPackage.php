<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;
use App\Models\FeaturePackage;
use App\Models\Central\Package;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends TenantModel
{
    //

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
    public function featurePackages()
    {
        return $this->hasMany(FeaturePackage::class, 'package_id',);
    }
}
