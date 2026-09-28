<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    //

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id');
    }


    public function library()
    {
        return $this->belongsTo(Library::class, 'library_id');
    }

}