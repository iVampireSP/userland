<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\YubicoOTP;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return view('admin.index');
    }

    public function showLoginForm(): View|RedirectResponse
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.index');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|max:50',
        ]);

        $otp = app(YubicoOTP::class);

        $otp->setOTP($request->input('otp'));

        $device_id = $otp->getDeviceID();

        $admin = (new Admin)->findByDeviceId($device_id);
        if (! $admin) {
            return redirect()->route('admin.login')->with('error', '设备不存在。');
        }

        if (! $otp->verify()) {
            return redirect()->route('admin.login')->with('error', 'OTP 不正确。');
        }

        auth('admin')->login($admin, true);

        return redirect()->route('admin.index');
    }

    public function logout(): RedirectResponse
    {
        auth('admin')->logout();

        return redirect()->route('admin.login');
    }
}
