<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;


class Category extends TenantModel
{

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
