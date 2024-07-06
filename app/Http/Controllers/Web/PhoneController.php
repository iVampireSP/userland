<?php

namespace App\Http\Controllers\Web;

use App\Contracts\SMS;
use App\Exceptions\SMS\SMSFailedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\SMSSupport;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PhoneController extends Controller
{
    protected SMS $sms;

    protected string $prefix = 'phone_bind_';

    public function __construct(SMSSupport $sms)
    {
        $this->sms = $sms;
    }

    // 绑定手机号
    public function create(Request $request)
    {
        if (! $request->user('web')->isPhoneVerified()) {
            return view('phone.bind');
        }

        return view('phone.index');
    }

    public function resend(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->back();
        }

        $user = $request->user('web');
        if ($user->isPhoneVerified()) {
            return redirect()->route('phone.index');
        }

        $exists = User::wherePhone($request->input('phone'))->exists();

        if ($exists) {
            return $this->badRequest('该手机号已被使用。');
        }

        // 检查缓存是否存在
        $cacheKey = $this->prefix.$request->input('phone');
        if (cache()->has($cacheKey)) {
            return $this->badRequest('验证码已发送，请稍后重试');
        }

        if ($request->user('web')->isPhoneVerified()) {
            return $this->badRequest('您已验证手机号');
        }

        $code = rand(1000, 9999);

        $request->validate([
            'phone' => 'required|string|max:11',
        ]);

        $this->sms->setPhone($request->input('phone'));

        try {
            $this->sms->validate();
        } catch (SMSFailedException $e) {
            return back()->withErrors(['phone' => $e->getMessage()]);
        }

        $this->sms->setTemplateId(config('settings.supports.sms.templates.verify_code'));
        $this->sms->setVariableContent([
            'code' => $code,
        ]);

        try {
            $this->sms->sendVariable();
        } catch (SMSFailedException|RequestException $e) {
            return $this->serverError($e->getMessage());
        }

        // 设置 key
        cache()->put($cacheKey, $code, config('settings.supports.sms.interval'));

        return $this->success();
    }

    public function store(Request $request)
    {
        $user = auth('web')->user();

        if ($user->isPhoneVerified()) {
            return redirect()->route('phone.index');
        }

        $request->validate([
            'phone' => 'required|string|max:11',
            'code' => 'required|string|max:4',
        ]);

        $exists = User::wherePhone($request->input('phone'))->exists();

        if ($exists) {
            return $this->badRequest('该手机号已被使用。');
        }

        $this->sms->setPhone($request->input('phone'));

        try {
            $this->sms->validate();
        } catch (SMSFailedException $e) {
            return back()->withErrors(['phone' => $e->getMessage()]);
        }

        $cacheKey = $this->prefix.$request->input('phone');

        if (! cache()->has($cacheKey)) {
            return back()->with('error', '验证码已过期');
        }

        try {
            if (cache()->get($cacheKey) !== $request->input('code')) {
                return back()->with('error', '验证码错误');
            }
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error($e->getMessage());

            return back()->with('error', '暂时无法验证。');
        }

        $user->phone = $request->input('phone');
        $user->phone_verified_at = now();
        $user->save();

        // 设置 phone_confirm session
        session()->put('auth.phone_confirmed_at', now());

        return redirect()->to(RouteServiceProvider::HOME)->with('success', '绑定成功');
    }

    public function edit(Request $request)
    {
        if (! $request->user('web')->isPhoneVerified()) {
            return view('phone.bind');
        }

        return view('phone.edit');
    }

    public function unbind(Request $request)
    {
        $user = auth('web')->user();
        if (! $user->isPhoneVerified()) {
            return view('phone.bind');
        }

        $user->phone_verified_at = null;
        $user->phone = null;

        $user->save();

        return redirect()->to(RouteServiceProvider::HOME)->with('success', '解绑成功');
    }

    public function show_validate_form(Request $request)
    {
        $user = $request->user('web');
        if (! $user->isPhoneVerified()) {
            return redirect()->route('phone.index');
        }

        return view('phone.verify');
    }

    public function send_validate_code(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->back();
        }

        $user = auth('web')->user();

        if (! $user->isPhoneVerified()) {
            return $this->badRequest('您还未绑定手机号');
        }

        try {
            $user->sendSmsVerificationCode();
        } catch (SMSFailedException $e) {
            return $this->serverError($e->getMessage());
        }

        return $this->success();
    }

    public function validate_code(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:4',
        ]);

        $user = auth('web')->user();

        if (! $user->isPhoneVerified()) {
            return $this->badRequest('您还未绑定手机号');
        }

        $code = $user->getSmsVerificationCode();

        if ($code !== $request->input('code')) {
            return $this->badRequest('验证码错误');
        }

        session(['auth.phone_confirmed_at' => now()]);

        return redirect()->to(RouteServiceProvider::HOME);
    }
}
