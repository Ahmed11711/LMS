<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $connection = 'tenant'; // مهم جداً عشان يشتغل على قاعدة الـ Tenant

    protected $fillable = ['key', 'value'];

    // Helper للقراءة السريعة
    public static function get($key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    // Helper للكتابة/التحديث
    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
