<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBagDownload extends Model
{
    protected $fillable = [
        'user_id',
        'bag_id',
        'bag_item_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bag()
    {
        return $this->belongsTo(Bag::class);
    }

    public function item()
    {
        return $this->belongsTo(BagItem::class, 'bag_item_id');
    }
}
