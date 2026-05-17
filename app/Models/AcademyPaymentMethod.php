<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyPaymentMethod extends Model
{
    //
    protected $casts = [
        'credentials' => 'json',
        'is_active'   => 'boolean',
    ];
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
