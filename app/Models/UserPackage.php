<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;
use App\Models\FeaturePackage;
use App\Models\Central\Package;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends TenantModel
{
    public $filterable = ['status', 'package_name', 'user_id', 'package_id'];
    public $sortable = ['id', 'user_id', 'created_at', 'updated_at', 'package_id', 'package_name', 'status'];


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
