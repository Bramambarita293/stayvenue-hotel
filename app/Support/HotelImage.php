<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

class HotelImage
{
    /**
     * URL publik foto (S3/Supabase). Tak pernah throw: env hilang, bucket
     * belum siap, atau path kosong -> fallback agar halaman tetap 200.
     */
    public static function url(?string $path, string $fallback = ''): string
    {
        if (! $path) {
            return $fallback;
        }

        try {
            return Storage::disk('s3')->url($path);
        } catch (Throwable) {
            return $fallback;
        }
    }
}
