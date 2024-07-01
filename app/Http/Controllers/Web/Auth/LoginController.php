<?php

namespace App\Http\Controllers\Web\Auth;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\FaceSupport;
use App\Support\MultiUserSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    protected MultiUserSupport $multiUser;

    public function __construct()
    {
        $this->multiUser = new MultiUserSupport();
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

        $faceSupport = new FaceSupport();
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

        return redirect()->intended(RouteServiceProvider::HOME);

    }

    public function showFaceLoginForm()
    {
        return view('faces.capture', [
            'type' => 'login',
        ]);
    }

    public function showLoginForm()
    {
        return view('auth.login');
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
}
