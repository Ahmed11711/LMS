<?php

namespace App\Models;

use App\Casts\StorageUrlCast;
use Illuminate\Database\Eloquent\Model;

class BagPurchase extends Model
{

    protected $casts = [
        'receipt' => StorageUrlCast::class,
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class, 'bag_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function paymentInfo()
    {
        return $this->belongsTo(PaymentInfo::class, 'payment_info_id');
    }
}
