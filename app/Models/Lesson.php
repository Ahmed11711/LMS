<?php

namespace App\Models;

use \App\Traits\TracksFeatureUsage;
use App\Models\BaseModel\TenantModel;

class Lesson extends TenantModel
{
    use TracksFeatureUsage;

    public function getFeatureSlug(): string
    {
        return 'storage_limit';
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function setVideoUrlAttribute($value): void
    {
        $this->attributes['video_url'] = stripslashes($value);
    }

    public function comments()
    {
        return $this->hasMany(LessonComment::class);
    }

    public function notes()
    {
        return $this->hasMany(LessonNote::class);
    }

    public function progresses()
    {
        return $this->hasMany(LessonProgress::class);
    }

    // progress اليوزر الحالي بس
    public function myProgress()
    {
        return $this->hasOne(LessonProgress::class)
            ->where('user_id', auth()->id());
    }
}
