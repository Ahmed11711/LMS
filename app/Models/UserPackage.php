<?php

namespace App\Models;

use App\Models\Central\Package;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
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
}
