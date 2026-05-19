<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseReceiverAccount extends Model
{
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructorReceiverAccount()
    {
        return $this->belongsTo(InstructorReceiverAccount::class, 'instructor_receiver_account_id');
    }
}
