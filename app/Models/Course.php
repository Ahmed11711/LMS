<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;
use App\Traits\TracksFeatureUsage;


class Course extends TenantModel
{
    use TracksFeatureUsage;
    public function getFeatureSlug(): string
    {
        return 'max_courses';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'course_id');
    }
}
