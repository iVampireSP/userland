<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
        $user = $request->user('api');
        $data = [
            'id' => $user->id,
            'avatar' => $user->avatar(),
        ];

        if ($user->tokenCan('profile')) {
            $data['name'] = $user->name;
            $data['email_verified_at'] = $user->email_verified_at;
            $data['real_name_verified_at'] = $user->real_name_verified_at;
        }

        if ($user->tokenCan('email')) {
            $data['email'] = $user->email;
        }

        if ($user->tokenCan('realname')) {
            $data['real_name'] = $user->real_name;
            $data['id_card'] = $user->id_card;
        }

        // append created_at
        $data['created_at'] = $user->created_at;

        return $this->success($data);
    }

    public function status(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->success(
                $request->user('api')->status()->first() ?? UserStatus::DEFAULT
            );
        }

        $request->validate([
            'text' => 'nullable|string',
        ]);

        // 如果用户有了状态，就更新，否则就创建
        $user = $request->user('api');

        $status = $user->status()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'text' => $request->input('text'),
        ]);

        return $this->success($status);
    }
}
