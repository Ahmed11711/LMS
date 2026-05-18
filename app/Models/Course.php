<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;
use App\Traits\TracksFeatureUsage;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends TenantModel
{
    use TracksFeatureUsage;
    public array $filterable = ['user_id', 'type', 'category_id'];

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

    public function infos()
    {
        return $this->hasMany(CourseInfo::class);
    }
    public function subscribers()
    {
        return $this->hasMany(UserSubscribe::class, 'course_id');
    }
    // Course.php
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
    // Course.php
    public function subscribes(): HasMany
    {
        return $this->hasMany(UserSubscribe::class);
    }
}
