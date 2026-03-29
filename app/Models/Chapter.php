<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;


class Chapter extends TenantModel
{
    //

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'chapter_id');
    }
}
