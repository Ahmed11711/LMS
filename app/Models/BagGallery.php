<?php

namespace App\Models;

use App\Casts\StorageUrlCast;
use Illuminate\Database\Eloquent\Model;

class BagGallery extends Model
{
    protected $casts = [
        'image' => StorageUrlCast::class,
    ];

    protected $fillable = [
        'bag_id',
        'image',
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }
}
