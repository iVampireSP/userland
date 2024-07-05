<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class QuickSupport
{
    public function create_face_register(string $image_b64)
    {
        // ttl 1 天
        $ttl = 60 * 60 * 24;

        Cache::put('quick:face_register', [
            'image_b64' => $image_b64,
        ], $ttl);
    }

    public function get_face_register()
    {
        return Cache::get('quick:face_register');
    }
}
