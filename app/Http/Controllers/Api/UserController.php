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

        $all_scopes = config('openid.passport.tokens_can');

        $scopes = [];

        foreach ($all_scopes as $scope_name => $scope_description) {
            if ($user->tokenCan($scope_name)) {
                $scopes[] = $scope_name;
            }
        }

        $data = $user->getClaims($scopes);


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
