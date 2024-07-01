<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

use function back;
use function redirect;
use function session;
use function view;

class AuthController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        return
            $request->user('web')?->with('status')
                ?
                view('index')
                :
                view('auth.login');
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

        $request->user()->delete();

        return redirect()->route('index')->with('success', '已删除用户。');
    }
}
