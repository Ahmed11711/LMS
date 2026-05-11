<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentInfo extends Model
{
    protected $casts = [
        'value' => 'array',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function paymentInfo()
    {
        return $this->belongsTo(PaymentInfo::class, 'payment_info_id');
    }
}
