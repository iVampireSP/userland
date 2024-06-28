<?php

namespace App\Http\Controllers\Web\Auth;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\FaceSupport;
use Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
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

        if (!$faces) {
            return back()->with('error', "找不到这位。");
        }

        if (count($faces) > 1) {
//            进入多账户选择
            // 只提取 user
            $users = $faces->map(function ($face) {
                return $face->user;
            });
            Session::put('switch-users', $users);

            return redirect()->route('login.select');
        }


        auth('web')->login($faces[0]->user, true);

        return redirect()->intended(RouteServiceProvider::HOME);

    }

    public function showFaceLoginForm()
    {
        return view('faces.capture', [
            'type' => 'login'
        ]);
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function logout(Request $request)
    {
        auth('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function selectAccount(Request $request)
    {
        // 如果不包含
        if (!Session::has('switch-users')) {
            return redirect(RouteServiceProvider::HOME)->with('error', '你无法进入本页面。');
        }

        $users = Session::get('switch-users');

        return view('auth.select', [
            'users' => $users
        ]);

    }

    public function switchAccount(Request $request)
    {
        // 如果不包含
        if (!Session::has('switch-users')) {
            return redirect(RouteServiceProvider::HOME)->with('error', '你不能选择账户。');
        }

        $users = Session::get('switch-users');

        $request->validate([
            'user_id' => "required"
        ]);

        $user_id = $request->input('user_id');

        $selected_user = null;

        foreach ($users as $user) {
            if ($user->id == $user_id) {
                $selected_user = $user;
                break;
            }
        }

        // if not found, return error
        if (!$selected_user) {
            return back()->with('error', '你无法选择此账户。');
        }

        auth('web')->login($selected_user, true);

        Session::forget('switch-users');

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
