<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BagsPayment extends Model
{
    use HasFactory;

    protected $table = 'bag_payments';

    protected $fillable = [
        'bag_id',
        'user_payment_info_id',
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }

    public function userPaymentInfo()
    {
        return $this->belongsTo(UserPaymentInfo::class);
    }
}
