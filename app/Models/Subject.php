<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    //

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

}