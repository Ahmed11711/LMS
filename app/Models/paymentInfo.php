<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class paymentInfo extends Model
{
    protected $casts = [
        'credentials' => 'array',
    ];
}
