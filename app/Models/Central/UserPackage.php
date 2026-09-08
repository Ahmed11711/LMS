<?php

namespace App\Models\Central;

use App\Models\Central\Package;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    protected $connection = 'LMS_CENTER';
    public $filterable = ['status', 'package_name', 'user_id', 'package_id'];
    public $sortable = ['id', 'user_id', 'created_at', 'updated_at', 'package_id', 'package_name', 'status'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($userPackage) {
            if (empty($userPackage->package_name)) {
                $userPackage->package_name = 'test';
            }
        });
    }
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
