<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\User\UserNotification;
use App\Support\Auth\RealNameSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RealNameController extends Controller
{
    public function payNotify(Request $request): bool
    {
        if (empty($request->all())) {
            return false;
        }

        $sign = $this->getSign($request->all());

        if ($sign === $request->input('sign')) {
            $signResult = true;
        } else {
            $signResult = false;
        }

        // get user real_name:user:out_trade_no:
        $user_id = Cache::get('real_name:user:'.$request->input('out_trade_no'));

        if ($signResult) {
            // 支付成功，添加实名认证资格
            Cache::put('real_name:user:'.$user_id, true, 60 * 60 * 24 * 30);
        }

        return $signResult;
    }

    public function getSign($param): string
    {
        ksort($param);
        $signStr = '';

        foreach ($param as $k => $v) {
            if ($k != 'sign' && $k != 'sign_type' && $v != '') {
                $signStr .= $k.'='.$v.'&';
            }
        }

        $signStr = substr($signStr, 0, -1);
        $signStr .= config('settings.supports.pay.mch_key');

        return md5($signStr);
    }
}
