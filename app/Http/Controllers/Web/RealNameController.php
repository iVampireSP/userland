<?php

namespace App\Http\Controllers\Web;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Support\RealNameSupport;
use Illuminate\Http\JsonResponse;
use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\View\Factory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Contracts\Foundation\Application;

class RealNameController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // 检测此用户是否有实名认证的资格（cache）
        if (! Cache::has('real_name:user:'.$request->user()->id)) {
            return back()->with('error', '您需要先购买实名认证的资格。');
        }

        $request->validate([
            'real_name' => 'required|string',
            'id_card' => 'required|string|size:18|unique:users,id_card',
        ]);

        $realNameSupport = new RealNameSupport();

        try {
            $birthday = $realNameSupport->getBirthday($request->input('id_card'));
        } catch (InvalidFormatException) {
            return back()->with('error', '身份证号码格式错误。');
        }

        // 检查年龄是否在区间内 settings.supports.real_name.min_age ~ settings.supports.real_name.max_age
        if (Carbon::now()->diffInYears($birthday) < config('settings.supports.real_name.min_age') || Carbon::now()->diffInYears($birthday) > config('settings.supports.real_name.max_age')) {
            $message = '至少需要 '.config('settings.supports.real_name.min_age').' 岁，最大 '.config('settings.supports.real_name.max_age').' 岁。';

            return back()->with('error', $message);
        }

        $user = $request->user();

        if ($user->real_name_verified_at !== null) {
            return back()->with('error', '您已经实名认证过了。');
        }

        try {
            $output = $realNameSupport->create($user->id, $request->input('real_name'), $request->input('id_card'));
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        Cache::set('real_name:user:'.$user->id, $output, 600);

        return redirect($output);
    }

    public function create(): View
    {
        return view('real_name.create');
    }

    public function pay(Request $request): \Illuminate\Contracts\View\View|Factory|\Illuminate\Foundation\Application|JsonResponse|RedirectResponse|Application
    {
        if ($request->ajax()) {
            $out_trade_no = Cache::get('real_name:user:'.$request->user()->id.':pay');

            if (! $out_trade_no) {
                return response()->json([
                    'code' => 0,
                    'message' => '订单号不存在。',
                ]);
            }

            $params = [
                'act' => 'order',
                'pid' => config('settings.supports.pay.mch_id'),
                'key' => config('settings.supports.pay.mch_key'),
                'out_trade_no' => $out_trade_no,
            ];

            $resp = Http::baseUrl(config('settings.supports.pay.url'))->asForm()->get('api.php', $params);

            if ($resp->successful()) {
                $data = $resp->json();

                if ($data['code'] === 1) {
                    // 是否已经支付
                    $data['status'] = intval($data['status']);
                    if ($data['status'] === 1) {
                        // 标记用户已经购买实名认证的资格，缓存 1 天
                        Cache::set('real_name:user:'.$request->user()->id, true, 86400);

                        return response()->json([
                            'code' => 1,
                            'message' => '支付成功。',
                        ]);
                    } else {
                        return response()->json([
                            'code' => 0,
                            'message' => '未支付。',
                        ]);
                    }
                } else {
                    return response()->json([
                        'code' => 0,
                        'message' => $data['msg'],
                    ]);
                }
            } else {
                return response()->json([
                    'code' => 0,
                    'message' => '请求支付接口失败。',
                ]);
            }
        }

        $request->validate([
            'type' => 'required|in:alipay,wxpay',
        ]);
        //
        // 检测此用户是否有实名认证的资格（cache）
        if (Cache::has('real_name:user:'.$request->user()->id)) {
            return back()->with('error', '您已经购买过实名认证的资格了。');
        }

        $out_trade_no = 'real-name-'.$request->user('web')->id.'-'.Carbon::now()->timestamp;

        $params = [
            'pid' => config('settings.supports.pay.mch_id'),
            'type' => $request->input('type'),
            'notify_url' => route('public.real-name.pay-notify'),
            'return_url' => route('public.real-name.pay-notify'),
            'out_trade_no' => $out_trade_no,
            'name' => '扫码支付',
            'money' => config('settings.supports.real_name.price'),
            'clientip' => $request->ip(),
        ];

        $public_real_name = new \App\Http\Controllers\Public\RealNameController();

        $sign = $public_real_name->getSign($params);

        $params['sign'] = $sign;
        $params['sign_type'] = 'MD5';

        try {
            $resp = Http::baseUrl(config('settings.supports.pay.url'))
            ->asForm()
            ->post('/mapi.php', $params);
        } catch (ConnectionException $e) {
            return back()->with('error', '无法解析计算机名称。');
        }

        if ($resp->status() !== 200) {
            return back()->with('error', '支付接口异常。');
        }

        $resp = $resp->json();

        if ($resp['code'] !== 1) {
            return back()->with('error', $resp['msg']);
        }

        // 缓存 600s
        Cache::set('real_name:user:'.$request->user()->id.':pay', $out_trade_no, 600);
        Cache::set('real_name:user:out_trade_no:'.$out_trade_no, $request->user()->id, 600);

        $qrcode = $resp['qrcode'];
        $type = $request->input('type');

        $qrcode = QrCode::size(150)->generate($qrcode);

        return view('real_name.pay', compact('qrcode', 'type'));
    }
}
