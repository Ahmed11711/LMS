<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;


class PhysicalCourseDetail extends TenantModel
{
    //

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
