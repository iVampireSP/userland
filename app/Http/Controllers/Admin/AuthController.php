<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\YubicoOTP;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
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

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|max:50',
        ]);

        $otp = app(YubicoOTP::class);

        $otp->setOTP($request->input('otp'));

        $device_id = $otp->getDeviceID();

        $admin = (new Admin)->findByDeviceId($device_id);
        if (!$admin) {
            // return redirect()->route('admin.login')->with('error', '设备不存在。');
            return response()->json([
                'status' => 'error',
                'error' => '设备不存在',
                'redirect' => route('admin.login'),
            ]);
        }

        if (!$otp->verify()) {
            // return redirect()->route('admin.login')->with('error', 'OTP 不正确。');
            return response()->json([
                'status' => 'error',
                'error' => 'OTP 不正确',
                'redirect' => route('admin.login'),
            ]);
        }

        auth('admin')->login($admin, true);

        // return redirect()->route('admin.index');
        return response()->json([
            'status' => 'success',
            'message' => '登录成功',
            'redirect' => route('admin.index'),
        ]);
    }

    public function logout(): RedirectResponse
    {
        auth('admin')->logout();

        return redirect()->route('admin.login');
    }
}
