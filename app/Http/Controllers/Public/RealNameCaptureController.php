<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Support\Auth\RealNameSupport;
use App\Support\Auth\IDCardSupport;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RealNameCaptureController extends Controller
{
    public function __construct(
        protected RealNameSupport $realNameSupport,
        protected IDCardSupport $idCardSupport,
    ) {
    }

    public function capture(Request $request, string $verification_id)
    {
        $cacheKey = 'application:real_name_verification:' . $verification_id;
        $verification = Cache::get($cacheKey);

        if (empty($verification)) {
            abort(404, '验证码不存在。');
        }

        // 续期 5 分钟
        Cache::put($cacheKey, $verification, now()->addMinutes(5));


        if ($request->post()) {
            $request->validate([
                'image_b64' => 'required|string',
            ]);

            $image_b64 = $request->input('image_b64');

            // 字符串大小不能超过 1mb
            if (strlen($image_b64) > 1024 * 1024) {
                return back()->with('error', '图片大小不能超过 1mb。');
            }

            // 检测是不是 data:image/jpeg;base64
            if (!preg_match('/^data:image\/jpeg;base64,/', $image_b64)) {
                return back()->with('error', '图片格式错误，请重新尝试。');
            }

            $realNameSupport = new RealNameSupport;
            try {
                $result = $realNameSupport->submit($verification['real_name'], $verification['id_card'], $image_b64);
            } catch (CommonException $e) {
                return back()->with('error', $e->getMessage());
            } catch (ConnectionException $e) {
                abort(500, '远程服务器没有返回预期的状态码。');
            }

            if ($result) {
                // 将缓存标记为 true
                $verification['status'] = 'success';
                Cache::put($cacheKey, $verification, now()->addMinutes(30));

                return redirect($verification['redirect_url']);
            } else {
                return back()->with('error', '实名认证失败。');
            }

        } else {
            return view('real_name.capture_application', [
                'verification' => $verification,
                'verification_id' => $verification_id,
            ]);
        }
    }
}
