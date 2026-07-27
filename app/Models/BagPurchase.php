<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagPurchase extends Model
{
    //

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