<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
    ];
    protected $allowedFields = ['id', 'title',  'is_active', 'created_at', 'updated_at'];
    protected $guarded = [];

    public function packageFeatures()
    {
        return $this->hasMany(FeaturePackage::class, 'package_id');
    }
    public function featurePackages()
    {
        return $this->hasMany(FeaturePackage::class, 'package_id');
    }
}
