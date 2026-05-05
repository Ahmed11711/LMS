<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscribe extends Model
{
    //

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function enrollments()
    {
        return $this->hasMany(UserSubscribe::class, 'course_id');
    }
}
