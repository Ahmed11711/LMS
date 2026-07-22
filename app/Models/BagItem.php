<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BagItem extends Model
{
    use HasFactory;

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
