<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function fastLogin(): \Illuminate\Http\JsonResponse
    {
        $random = Str::random(64);

        Cache::put('session_login:' . $random, auth('api')->id(), 60);

        return $this->success(['url' => route('auth.fast-login', ['token' => $random])]);
    }

    public function realName(): \Illuminate\Http\JsonResponse
    {
        $user = auth('api')->user();

        return $this->success([
            'real_name' => $user->real_name,
            'id_card' => $user->id_card,
            'real_name_verified_at' => $user->real_name_verified_at,
        ]);
    }

    public function user(Request $request)
    {
        return $request->user('api');
    }
}
