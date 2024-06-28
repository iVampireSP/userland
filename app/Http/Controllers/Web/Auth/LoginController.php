<?php

namespace App\Http\Controllers\Web\Auth;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\FaceSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            $face = $faceSupport->search($embedding);
        } catch (CommonException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (!$face) {
            return back()->with('error', "找不到这位。");
        }

        auth('web')->login($face->user, true);

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
}
