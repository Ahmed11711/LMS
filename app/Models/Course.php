<?php

namespace App\Models;

use \App\Models\Template;
use \App\Models\UserSubscribe;
use App\Casts\StorageUrlCast;
use App\Models\BaseModel\TenantModel;
use App\Traits\TracksFeatureUsage;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends TenantModel
{
    use TracksFeatureUsage;
    protected $casts = [
        'image' => StorageUrlCast::class,
    ];
    public array $filterable = ['user_id', 'type', 'category_id'];

    public function getFeatureSlug(): string
    {
        return 'max_courses';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function activeSubscribers()
    {
        return $this->hasMany(UserSubscribe::class)
            ->where('status', 'active');
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
    public function courseReceiverAccounts()
    {
        return $this->hasMany(CourseReceiverAccount::class);
    }
    public function userSubscribes()
    {
        return $this->hasMany(UserSubscribe::class, 'course_id');
    }
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
