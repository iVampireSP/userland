<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Support\Auth\IDCardSupport;
use App\Support\Auth\RealNameSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Str;

class RealNameController extends Controller
{
    public function __construct(
        protected RealNameSupport $realNameSupport,
        protected IDCardSupport $idCardSupport,
    ) {
        $this->middleware('auth:application');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'real_name' => 'required|string',
            'id_card' => 'required|string|size:18',
            'redirect_url' => 'required|string',
        ]);

        $id_card = $request->input('id_card');

        $validate = $this->idCardSupport->isValid($id_card);

        if (! $validate) {
            return response()->json([
                'error' => '身份证校验失败。',
            ], 422);
        }

        $age = $this->realNameSupport->getAge($id_card);

        // 检查年龄是否在区间内 settings.supports.real_name.min_age ~ settings.supports.real_name.max_age
        if ($age < config('settings.supports.real_name.min_age') || $age > config('settings.supports.real_name.max_age')) {
            $message = '至少需要 '.config('settings.supports.real_name.min_age').' 岁，最大 '.config('settings.supports.real_name.max_age').' 岁。';

            return response()->json([
                'error' => $message,
            ], 422);
        }

        $randomId = Str::random(32);
        $expiresAt = now()->addMinutes(10);

        Cache::put('application:real_name_verification:'.$randomId, [
            'real_name' => $request->input('real_name'),
            'id_card' => $request->input('id_card'),
            'status' => 'pending',
            'redirect_url' => $request->input('redirect_url'),
        ], $expiresAt);

        return response()->json([
            'verification_id' => $randomId,
            'verification_url' => route('applications.real_name.show', $randomId),
            'captcha_url' => route('public.applications.real_name.capture', $randomId),
            'expires_at' => $expiresAt,
        ]);
    }

    public function show(Request $request, string $verification_id): JsonResponse
    {
        $verification = Cache::get('application:real_name_verification:'.$verification_id);

        // 获取状态
        $status = $verification['status'];

        if (empty($status)) {
            return response()->json([
                'status' => 'not_found',
                'error' => '验证码不存在。',
            ], 404);
        }

        return response()->json($verification);
    }
}
