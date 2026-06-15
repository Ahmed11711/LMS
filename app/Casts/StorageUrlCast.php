<?php
// app/Casts/StorageUrlCast.php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class StorageUrlCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) return $value;

        return asset(ltrim($value, '/'));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }
}
