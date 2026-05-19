<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorReceiverAccount extends Model
{
    //

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function receiverAccount()
    {
        return $this->belongsTo(ReceiverAccount::class, 'receiver_account_id');
    }

}