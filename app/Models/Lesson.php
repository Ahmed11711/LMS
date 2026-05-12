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
    // app/Models/Lesson.php

    public function setVideoUrlAttribute($value): void
    {
        $this->attributes['video_url'] = stripslashes($value);
    }
}
