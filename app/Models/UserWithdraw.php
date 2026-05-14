<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWithdraw extends Model
{

    public $filterable = ['status'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function userPaymentInfo()
    {
        return $this->belongsTo(UserPaymentInfo::class, 'user_payment_info_id');
    }


    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
