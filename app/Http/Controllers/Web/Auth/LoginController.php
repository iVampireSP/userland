<?php

namespace App\Http\Controllers\Web\Auth;

use App\Contracts\SMS;
use App\Exceptions\CommonException;
use App\Exceptions\SMS\SMSFailedException;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\FaceSupport;
use App\Support\MultiUserSupport;
use App\Support\SMSSupport;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LoginController extends Controller
{
    protected MultiUserSupport $multiUser;

    protected SMS $sms;

    protected string $prefix = 'sms_login_';

    public function __construct()
    {
        $this->sms = new SMSSupport;
        $this->multiUser = new MultiUserSupport;
    }

    public function passwordLogin(Request $request)
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);

        // 查询用户（邮箱或手机号）
        $user = User::where('email', $request->account)
            ->orWhere('phone', $request->account)
            ->first();

        if (! $user) {
            return back()->with('error', __('auth.failed'));
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->with('error', __('auth.password'));
        }

        $this->multiUser->add($user);

        auth('web')->login($user, true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function faceLogin(Request $request)
    {
        $request->validate([
            'image_b64' => 'required|string',
        ]);

        $image_b64 = $request->input('image_b64');

        $faceSupport = new FaceSupport;
        try {
            $faceSupport->check($image_b64);
            $embedding = $faceSupport->test_image($image_b64);
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            $faces = $faceSupport->search($embedding);
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! $faces) {
            return back()->with('error', '找不到对应的用户。');
        }

        if (count($faces) > 1) {
            // 只提取 user
            $faces->map(function ($face) {
                $this->multiUser->add($face->user);
            });

            return redirect()->to($this->multiUser->url());
        }

        $user = $faces->first()?->user;
        if (! $user) {
            return back()->with('error', '寻找用户的时候出现了问题，可能这个账户现在已经被注销。');
        }
        $this->multiUser->add($user);
        $this->multiUser->switch($user);

        return redirect()->intended(RouteServiceProvider::HOME)->with('success', '登录成功。');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showCustomLoginForm(Request $request, Client $client)
    {
        $client->load('user');

        return view('auth.custom_login', compact('client'));
    }

    public function sendSMS(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->back();
        }

        $request->validate([
            'phone' => 'required',
        ]);

        // 检查缓存是否存在
        $cacheKey = $this->prefix.$request->input('phone');
        if (cache()->has($cacheKey)) {
            return $this->badRequest('验证码已发送，请稍后重试');
        }

        //        $user = User::wherePhone($request->input('phone'))->limit(5)->first();

        $this->sms->setPhone($request->input('phone'));
        try {
            $this->sms->validate();
        } catch (SMSFailedException $e) {
            return $this->serverError($e->getMessage());
        }

        $code = rand(1000, 9999);

        $cacheKey = $this->prefix.$request->input('phone');

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

    public function SMSValidate(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $request->validate([
            'phone' => 'required|string|max:11',
            'code' => 'required|string|max:4',
        ]);

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

        // 成功
        $user = User::wherePhone($request->input('phone'))->limit(5)->first();

        if (! $user) {
            // 为注册，将自动创建用户
            $user = User::create([
                'name' => $request->input('phone'),
                'phone' => $request->input('phone'),
                'password' => Hash::make(Str::random(18)),
                'phone_verified_at' => now(),
            ]);
        }

        auth('web')->login($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function tokenLogin(Request $request)
    {
        $request->validate([
            'token' => 'string|required',
        ]);

        $user = (new User)->getLoginToken(
            token: $request->input('token'),
            prefix: 'token',
        );

        if (! $user) {
            return back()->with('error', '找不到对应的登录请求。');
        }

        $this->multiUser->add($user);
        $this->multiUser->switch($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function logout()
    {
        $user = auth('web');
        $success = $this->multiUser->remove($user->user());

        $user->logout();

        if (! $success) {
            return back()->with('error', '切换用户失败。');
        }

        return redirect('/');
    }

    public function logoutAll(Request $request)
    {
        // 刷新 session
        auth()->guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
