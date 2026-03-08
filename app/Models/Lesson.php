<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    //

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
