<?php

namespace App\Models;

use App\Casts\StorageUrlCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bag extends Model
{
    use HasFactory;

    protected $casts = [
        'image' => StorageUrlCast::class,
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'download_limit' => 'integer',
        'download_validity_days' => 'integer',
        'count_view' => 'integer',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'short_description',
        'description',
        'image',
        'category_bag_id',
        'type_price',
        'price',
        'discount_price',
        'currency',
        'download_type',
        'download_limit',
        'download_validity_days',
        'count_view',
        'status',
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
            'bag_id',
            'user_payment_info_id'
        );
    }

    public function gallery()
    {
        return $this->hasMany(BagGallery::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryBag::class, 'category_bag_id');
    }
}
