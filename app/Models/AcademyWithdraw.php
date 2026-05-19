<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyWithdraw extends Model
{
    //

    public function academy()
    {
        return $this->belongsTo(User::class, 'academy_id');
    }
}
