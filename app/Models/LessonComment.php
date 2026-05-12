<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonComment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(LessonComment::class, 'parent_id');
    }

    public function likes()
    {
        return $this->hasMany(LessonCommentLike::class);
    }
}
