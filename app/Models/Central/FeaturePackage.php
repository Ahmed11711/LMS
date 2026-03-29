<?php

namespace App\Models\Central;

use App\Models\Central\Features;
use App\Models\Central\Package;
use Illuminate\Database\Eloquent\Model;

class FeaturePackage extends Model
{
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }


    public function feature()
    {
        return $this->belongsTo(Features::class, 'feature_id');
    }
}
