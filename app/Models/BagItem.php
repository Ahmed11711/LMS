<?php

namespace App\Models;

use App\Casts\StorageUrlCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BagItem extends Model
{
    use HasFactory;
    protected $casts = [
        'path' => StorageUrlCast::class,
    ];
    protected $fillable = [
        'bag_id',
        'path',
        'type',
    ];

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }
}
