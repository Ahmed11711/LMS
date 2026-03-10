<?php

namespace App\Models;

use App\Models\BaseModel\TenantModel;


class Lesson extends TenantModel
{


    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
