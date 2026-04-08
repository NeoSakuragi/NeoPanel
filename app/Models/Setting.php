<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'encrypted' => $setting->value ? Crypt::decryptString($setting->value) : $default,
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $storeValue = $type === 'encrypted' && $value
            ? Crypt::encryptString($value)
            : $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storeValue, 'type' => $type],
        );
    }
}
