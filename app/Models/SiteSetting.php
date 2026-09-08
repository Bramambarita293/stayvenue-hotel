<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    public const AUTH_LOGIN_COVER = 'auth_login_cover';

    public const AUTH_REGISTER_COVER = 'auth_register_cover';

    protected $fillable = ['key', 'value'];

    /**
     * Ambil nilai setting (di-cache). Hilang/rusak -> default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever('site_settings.'.$key, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('site_settings.'.$key);
    }
}
