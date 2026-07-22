<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bag extends Model
{
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
        return $this->belongsToMany(UserPaymentInfo::class, 'bag_payments');
    }
}
