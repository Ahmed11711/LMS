<?php

namespace App\Models;

use App\Casts\StorageUrlCast;
use Illuminate\Database\Eloquent\Model;

class ReceiverAccount extends Model
{
    protected $casts = [
        'logo' => StorageUrlCast::class,
    ];
}
