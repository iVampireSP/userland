<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
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

    public function logout(Request $request)
    {
        auth('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
