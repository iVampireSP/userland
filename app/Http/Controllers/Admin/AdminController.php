<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\YubicoOTP;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $admins = (new Admin)->paginate(50);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email',
            'name' => 'required|string|max:30',
            'otp' => 'required|string|max:50',
        ]);

        $otp = app(YubicoOTP::class);
        $otp->setOTP($request->input('otp'));
        if (! $otp->verify()) {
            return redirect()->back()->with('error', 'OTP 验证失败。')->withInput();
        }
        $device_id = $otp->getDeviceID();

        $admin = (new Admin)->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        try {
            $admin->addYubicoOTPDevice($device_id);
        } catch (Exception $e) {
            return redirect()->back()->with('error', '添加 YubiKey 失败，原因 '.$e->getMessage())->withInput();
        }

        return redirect()->route('admin.admins.edit', $admin)->with('success', '管理员创建成功。');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.admins.create');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin): View
    {
        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email,'.$admin->id,
            'name' => 'required|string|max:30',
        ]);

        $msg = '管理员信息更新成功。';

        $admin->name = $request->input('name');
        $admin->email = $request->input('email');

        $admin->save();

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin): RedirectResponse
    {
        // 不能删除自己
        if ($admin->id == auth('admin')->id()) {
            return redirect()->back()->with('error', '不能删除自己。');
        }

        // 不能删除最后一个管理员
        if ((new Admin)->count() == 1) {
            return redirect()->back()->with('error', '不能删除最后一个管理员。');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', '管理员已删除。');
    }
}
