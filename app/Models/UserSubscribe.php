<?php

namespace App\Models;

use App\Casts\StorageUrlCast;
use Illuminate\Database\Eloquent\Model;

class UserSubscribe extends Model
{
    protected $casts = [
        'receipt' => StorageUrlCast::class,
    ];

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
