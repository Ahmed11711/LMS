<?php

namespace App\Models;


class Course extends TenantModel
{
    //

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
