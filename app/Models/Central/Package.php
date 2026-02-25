<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $guarded = [];

    public function packageFeatures()
    {
        return $this->hasMany(FeaturePackage::class, 'package_id');
    }
}
