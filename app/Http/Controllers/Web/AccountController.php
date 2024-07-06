<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Jobs\UserDeleteJob;
use App\Mail\EmailChange;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\MultiUserSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

use function back;
use function redirect;
use function session;
use function view;

class AccountController extends Controller
{
    protected MultiUserSupport $multiUser;

    protected string $change_email_prefix = 'user:change-email:';

    public function __construct()
    {
        $this->multiUser = new MultiUserSupport();
    }

    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        if ($request->ajax()) {
            return $this->success($request->user('web'));
        }

        return view('index');
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'nullable|sometimes|string|max:255',
            'receive_marketing_email' => 'nullable|sometimes|boolean',
        ]);

        $user = $request->user('web');

        $user->update($request->only('name', 'receive_marketing_email'));

        if ($request->ajax()) {
            return $this->success($user->only('name', 'receive_marketing_email'));
        }

        return back()->with('info', '用户资料已更新。');
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();

        session()->regenerateToken();

        return redirect()->route('index');
    }

    public function fastLogin(string $token): RedirectResponse
    {
        $cache_key = 'session_login:'.$token;
        $user_id = Cache::get('session_login:'.$token);

        if (empty($user_id)) {
            return redirect()->route('index')->with('error', '登录请求的 Token 不存在或已过期。');
        }

        $user = (new User)->find($user_id);

        if (empty($user)) {
            return redirect()->route('index')->with('error', '无法验证。');
        }

        Auth::guard('web')->login($user);

        Cache::forget($cache_key);

        return redirect()->route('index');
    }

    public function status(Request $request): RedirectResponse
    {
        $request->validate([
            'emoji' => 'nullable|string',
            'status' => 'nullable|string',
            'text' => 'nullable|string',
        ]);

        // 如果用户有了状态，就更新，否则就创建
        $request->user('web')->status()->updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'text' => $request->input('text'),
        ]);

        return back()->with('success', '已更新用户状态。');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // 验证密码
        if (! Auth::guard('web')->attempt([
            'email' => $request->user()->email,
            'password' => $request->input('password'),
        ])) {
            return back()->with('error', '密码错误。');
        }

        dispatch(new UserDeleteJob($request->user('web')));

        return redirect()->route('index')->with('success', '已请求删除。');
    }

    public function selectAccount()
    {
        return view('auth.select', [
            'users' => $this->multiUser->get(),
        ]);
    }

    public function switchAccount(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
        ]);

        // logout
        auth('web')->logout();

        $users = $this->multiUser->get();

        if (! $users->count()) {
            return back()->with('error', '你没有登录过其他账号。');
        }

        $user = $users->firstWhere('id', $request->input('user_id'));

        if (! $this->multiUser->contains($user)) {
            return back()->with('error', '会话中没有找到此用户。');
        }

        $login = $this->multiUser->switch($user);

        if (! $login) {
            return back()->with('error', '切换用户失败。');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function showChangeEmailForm(Request $request)
    {
        //        $user = $request->user('web');
        //        $user_key = $this->change_email_prefix.'users:'.$user->id;

        //        $exists = Cache::has($user_key);

        return view('user.change_email');
    }

    public function sendChangeEmail(Request $request)
    {
        // 检测邮箱是否存在
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = $request->user('web');

        $user_key = $this->change_email_prefix.'users:'.$user->id;

        // 检查是否已经申请过
        if (Cache::has($user_key)) {
            return back()->with('error', '您已经申请过更改邮箱，24 小时内只能更改一次。');
        }

        if ($user->email === $request->input('email')) {
            return back()->with('error', '新邮箱不能和旧邮箱一样。');
        }
        $token = Str::random(128);

        $token_key = $this->change_email_prefix.'tokens:'.$token;

        $exists = User::where('email', $request->input('email'))->exists();

        Cache::put($token_key, [
            'user_id' => $user->id,
            'email' => $request->input('email'),
        ], 60 * 60 * 24);
        Cache::put($user_key, $token, 60 * 60 * 24);

        // 发送验证
        $job = new SendMailJob($request->input('email'), new EmailChange(
            token: $token,
            email: $request->input('email'),
            user: $user,
        ));
        dispatch($job);

        $r = back()->with('success', '我们已向新邮箱发送了一封邮件，请于 24 小时内完成验证。');

        if ($exists) {
            $r->with('info', '这个邮箱绑定了其他账户，如果您确认更改，则原先的账户的邮箱将被解绑。');
        }

        return $r;

    }

    public function changeEmail(Request $request, string $token)
    {
        $token_key = $this->change_email_prefix.'tokens:'.$token;

        if (! Cache::has($token_key)) {
            return redirect()->to(RouteServiceProvider::HOME)->with('error', '找不到对应的请求。');
        }

        $info = Cache::get($token_key);

        $user_id = $info['user_id'];
        $email = $info['email'];

        if ($user_id != $request->user('web')->id) {
            return redirect()->to(RouteServiceProvider::HOME)->with('error', '你必须登录为 '.$email.' 才能更改。');
        }

        // 检查原来的用户
        $origin_email_user = User::whereEmail($email)->first();

        if ($origin_email_user) {
            if ($origin_email_user->id === $user_id) {
                $origin_email_user->email = $email;
                $origin_email_user->email_verified_at = now();
            } else {
                $origin_email_user->email = null;
                $origin_email_user->email_verified_at = null;
            }

            $origin_email_user->save();
        }
        // 如果原来的邮箱没有用户
        $user = User::find($user_id);
        if (! $user) {
            return redirect()->to(RouteServiceProvider::HOME)->with('error', '无法找到用户。');
        } else {
            $user->email = $email;
            $user->email_verified_at = now();
            $user->save();
        }

        Cache::forget($token_key);
        // 24 小时只能修改一次。
        //        Cache::forget($this->change_email_prefix.'users:'.$request->user('web')->id);

        return redirect()->to(RouteServiceProvider::HOME)->with('success', '邮箱更改成功。');
    }
}
