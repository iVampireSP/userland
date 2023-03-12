<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function fastLogin(): JsonResponse
    {
        $random = Str::random(64);

        Cache::put('session_login:'.$random, auth('api')->id(), 60);

        return $this->success(['url' => route('auth.fast-login', ['token' => $random])]);
    }

    public function realName(): JsonResponse
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

    public function status(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->success(
                $request->user('api')->status()->first() ?? UserStatus::DEFAULT
            );
        }

        $request->validate([
            'emoji' => 'nullable|string',
            'status' => 'nullable|string',
            'text' => 'nullable|string',
        ]);

        // 如果用户有了状态，就更新，否则就创建
        $user = $request->user('api');

        $status = $user->status()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'emoji' => $request->input('emoji'),
            'status' => $request->input('status'),
            'text' => $request->input('text'),
        ]);

        return $this->success($status);
    }
}
