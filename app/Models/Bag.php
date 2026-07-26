<?php

namespace App\Models;

use App\Casts\StorageUrlCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bag extends Model
{

    protected $casts = [
        'image' => StorageUrlCast::class,
    ];
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'short_description',
        'description',
        'image',
        'category_name',
        'type_price',
        'price',
        'discount_price',
        'count_download',
        'count_view',
        'is_active',
    ];

    public function items()
    {
        return $this->hasMany(BagItem::class);
    }

    public function userPaymentInfos()
    {
        return $this->belongsToMany(
            InstructorReceiverAccount::class,
            'bag_payments',
            'bag_id',                  // المفتاح بتاع Bag في الـ pivot
            'user_payment_info_id'     // المفتاح بتاع InstructorReceiverAccount في الـ pivot
        );
    }
}
