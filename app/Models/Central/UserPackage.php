<?php

namespace App\Models\Central;

use App\Models\Central\Package;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    //
    protected static function boot()
    {
        parent::boot();

        // قبل عملية الحفظ في الداتابيز مباشرة
        static::creating(function ($userPackage) {
            // لو الـ package_name فاضي، حط القيمة الافتراضية "test"
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
