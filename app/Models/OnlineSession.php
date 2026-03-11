<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;


class OnlineSession extends TenantModel
{
    //

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
