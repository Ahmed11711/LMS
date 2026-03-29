<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Features extends Model
{
    public static function booted()
    {
        static::saving(function ($feature) {
            if ($feature->title) {
                $feature->key = Str::of($feature->title)
                    ->replace(' ', '_')
                    ->upper();
            }
        });
    }
}
